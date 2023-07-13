<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Patient;
use App\Models\Person;
use App\Models\MedicalRecord;
use App\Models\FamilyRecord;
use App\Models\LegalRepresentative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Carbon\Carbon;

class PatientsController extends Controller
{
    //funcion para obtener todos los pacientes
    public function getPatients()
    {
        $patients = patient::where('archived', false)->get();

        $patients->load(['person', 'type', 'medical_record.diabetes_type',]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients' => $patients
            ]
        ]);
    }

    //función para obtener paciente por id
    public function getPatientById($id)
    {
        $patient = patient::where('id', $id)
            ->where('archived', false)
            ->first();
        if (!$patient) {
            return response()->json([
                'message' => 'Paciente no encontrado'
            ]);
        }

        $patient->load([
            'type',
            'person',
            'person.region',
            'person.nationality',
            'person.identification_type',
            'medical_record',
            'medical_record.diabetes_type',
            'medical_record.diagnostic_period',
            'medical_record.basal_insulin_type',
            'medical_record.prandial_insulin_type',
            'medical_record.hospital_type',
            'medical_record.doctor',
            'medical_record.hospital',
            'medical_record.assistance_type',
            'family_record',
            'family_record.legal_representative',
            'family_record.legal_representative.person',
            'family_record.legal_representative.person.identification_type',
            'family_record.legal_representative.person.nationality'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'patient' => $patient
            ]
        ]);
    }

    public function searchPatientsByTerm($term = '')
    {
        $patients = patient::where(
            'archived',
            false
        )
            ->where(function ($query) use ($term) {
                $query->where('email', 'like', '%' . $term . '%')
                    ->orWhereHas('person', function ($query) use ($term) {
                        $query->where('names', 'like', '%' . $term . '%')
                            ->orWhere('last_names', 'like', '%' . $term . '%')
                            ->orWhere('identification', 'like', '%' . $term . '%')
                            ->orWhereRaw("concat(names, ' ', last_names) like ?", ['%' . $term . '%'])
                            ->orWhereHas('identification_type', function ($query) use ($term) {
                                $query->where('value', 'like', '%' . $term . '%');
                            });
                    });
            })
            ->get();

        $patients->load(['person', 'type', 'medical_record.diabetes_type',]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients' => $patients
            ]
        ]);
    }

    /**
     * Crea un paciente con la información proporcionada en la petición.
     *
     * @param Request $request La petición HTTP que contiene los datos del paciente y su historial médico.
     * @return void
     */
    public function createPatient(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $person = Person::create(array_merge(
                    $request->person,
                    [
                        'date_birth' => date('Y-m-d', strtotime($request->person['date_birth'])),
                    ]
                ));

                $medicalRecord = MedicalRecord::create(array_merge(
                    $request->medical_record,
                    [
                        'diagnosis_date' => date('Y-m-d', strtotime($request->medical_record['diagnosis_date'])),
                        'last_hb_test' => date('Y-m-d', strtotime($request->medical_record['last_hb_test'])),
                        'last_visit' => date('Y-m-d', strtotime($request->medical_record['last_visit'])),
                        'doctor' => is_string($request->medical_record['doctor'])
                            ? Catalog::firstOrCreate(['type' => 'medicos', 'value' => $request->medical_record['doctor']])->id
                            : $request->medical_record['doctor']['id'],
                        'hospital' => is_string($request->medical_record['hospital'])
                            ? Catalog::firstOrCreate(['type' => 'hospitales', 'value' => $request->medical_record['hospital']])->id
                            : $request->medical_record['hospital']['id'],
                    ]
                ));

                $familyRecordData = $request->family_record;
                $hasLegalRepresentative = false;

                // Verificar si el paciente es menor de edad o tiene discapacidad
                if (Carbon::parse($request->person['date_birth'])->age < 18 || $request->person['disability'] === 'si') {
                    $personLegalRepresentative = Person::create($familyRecordData['legal_representative']['person']);

                    $legalRepresentative = LegalRepresentative::create(array_merge(
                        $familyRecordData['legal_representative'],
                        ['person' => $personLegalRepresentative->id]
                    ));

                    $familyRecordData['legal_representative'] = $legalRepresentative->id;
                    $hasLegalRepresentative = true;
                }

                $familyRecord = FamilyRecord::create(array_merge(
                    $familyRecordData,
                    ['legal_representative' => $hasLegalRepresentative ? $familyRecordData['legal_representative'] : null]
                ));

                Patient::create(
                    [
                        'email' => $request->email,
                        'type' => $request->type,
                        'person' => $person->id,
                        'medical_record' => $medicalRecord->id,
                        'family_record' => $familyRecord->id
                    ]
                );
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Paciente creado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el paciente: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updatePatient(
        Request $request,
        $id
    ) {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'message' => 'Paciente no encontrado'
            ]);
        }

        try {
            DB::transaction(function () use ($request, $patient) {
                $person = Person::find($patient->person);
                $person->fill($request->person);
                $person->date_birth = date('Y-m-d', strtotime($request->person['date_birth']));
                $person->save();

                $medicalRecord = MedicalRecord::find($patient->medical_record);
                $medicalRecord->fill($request->medical_record);
                $medicalRecord->diagnosis_date = date('Y-m-d', strtotime($request->medical_record['diagnosis_date']));
                $medicalRecord->last_hb_test = date('Y-m-d', strtotime($request->medical_record['last_hb_test']));
                $medicalRecord->last_visit = date('Y-m-d', strtotime($request->medical_record['last_visit']));

                if (isset($request->medical_record['doctor'])) {
                    if (is_string($request->medical_record['doctor'])) {
                        $doctor = Catalog::firstOrCreate([
                            'type' => 'medicos',
                            'value' => $request->medical_record['doctor']
                        ]);
                        $medicalRecord->doctor = $doctor->id;
                    } else {
                        $medicalRecord->doctor = $request->medical_record['doctor']['id'];
                    }
                }
                if (isset($request->medical_record['hospital'])) {

                    if (is_string($request->medical_record['hospital'])) {
                        $hospital = Catalog::firstOrCreate([
                            'type' => 'hospitales',
                            'value' => $request->medical_record['hospital']
                        ]);
                        $medicalRecord->hospital = $hospital->id;
                    } else {
                        $medicalRecord->hospital = $request->medical_record['hospital']['id'];
                    }
                }
                $medicalRecord->save();

                $familyRecord = FamilyRecord::find($patient->family_record);

                if (Carbon::parse($request->person['date_birth'])->age < 18 || $request->person['disability'] === 'si') {
                    if (!$familyRecord->legal_representative) {
                        $legalRepresentative = new LegalRepresentative();
                        $personLegalRepresentative = new Person();
                        $legalRepresentative->person = $personLegalRepresentative->id;
                    } else {
                        $legalRepresentative = LegalRepresentative::find($familyRecord->legal_representative);
                        $personLegalRepresentative = Person::find($legalRepresentative->person);
                    }
                    $personLegalRepresentative->fill($request->family_record['legal_representative']['person']);
                    $personLegalRepresentative->save();

                    $legalRepresentative->fill($request->family_record['legal_representative']);
                    $legalRepresentative->person()->associate($personLegalRepresentative);
                    $legalRepresentative->save();

                    $familyRecord->legal_representative()->associate($legalRepresentative);
                    $familyRecord->save();
                } else {

                    if ($familyRecord->legal_representative) {
                        $legalRepresentative = LegalRepresentative::find($familyRecord->legal_representative);
                        $personLegalRepresentative = Person::find($legalRepresentative->person);
                        $familyRecord->legal_representative = null;
                        $familyRecord->save();

                        DB::transaction(function () use ($legalRepresentative, $personLegalRepresentative) {
                            $legalRepresentative->delete();
                            $personLegalRepresentative->delete();
                        });
                    }
                }

                $patient->update([
                    'type' => $request->type,
                    'email' => $request->email
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Paciente actualizado correctamente',
                'data' => [
                    'patient' => $patient
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el paciente: ' . $e->getMessage()
            ], 500);
        }
    }


    public function searchPatientsArchivedByTerm($term = '')
    {
        $patients = Patient::where('archived', true)
            ->where(function ($query) use ($term) {
                $query->where('email', 'like', '%' . $term . '%')
                    ->orWhereHas('person', function ($query) use ($term) {
                        $query->where('identification', 'like', '%' . $term . '%')
                            ->orWhere('names', 'like', '%' . $term . '%')
                            ->orWhere('last_names', 'like', '%' . $term . '%')
                            ->orWhereRaw("concat(names, ' ', last_names) like ?", ['%' . $term . '%'])
                            ->orWhereHas('identification_type', function ($query) use ($term) {
                                $query->where('value', 'like', '%' . $term . '%');
                            });
                    });
            })->get();

        $patients->load(['person', 'archived_by']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients' => $patients
            ]
        ]);
    }

    public function getArchivedPatients()
    {
        $patients = patient::where('archived', true)->get();

        $patients->load(['person', 'archived_by']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients' => $patients
            ]
        ]);
    }

    public function archivePatient($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json([
                'message' => 'Paciente no encontrado'
            ]);
        }
        $patient->archived = true;
        $patient->archived_at = now();
        $patient->archived_by = auth()->user()->id;
        $patient->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Paciente archivado correctamente',
        ]);
    }

    public function restorePatient($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json([
                'message' => 'Paciente no encontrado'
            ]);
        }
        $patient->archived = false;
        $patient->archived_at = null;
        $patient->archived_by = null;
        $patient->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Paciente restaurado correctamente',
        ]);
    }

    public function deletePatient($id)
    {
        try {
            DB::transaction(
                function () use ($id) {

                    $patient = Patient::find($id);
                    if (!$patient) {
                        return response()->json([
                            'message' => 'paciente no encontrado'
                        ]);
                    }
                    $patient->delete();
                    Person::find($patient->person)->delete();
                }
            );

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Paciente eliminado correctamente',
            ]);
        } catch (ModelNotFoundException $ex) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Paciente no encontrado'
            ], 500);
        } catch (\Illuminate\Database\QueryException $ex) {
            if ($ex->getCode() == 23000) {
                return new JsonResponse([
                    'status' => 'alert',
                    'message' => 'No se puede eliminar el paciente porque tiene muchos registros'
                ], 500);
            } else {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Error al eliminar el paciente'
                ], 500);
            }
        } catch (\Exception $ex) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al eliminar el paciente'
            ], 500);
        }
    }

    function getPatientsPerMonthPerYear($year)
    {
        $result = [];

        $patientsPerMonth = Patient::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count'),
            DB::raw('(SELECT COUNT(*) FROM patients p2 WHERE YEAR(p2.created_at) <= YEAR(p.created_at) AND MONTH(p2.created_at) <= MONTH(p.created_at)) as total')
        )
            ->from('patients as p')
            ->groupBy('year', 'month', 'created_at')
            ->get();

        $patientsPerMonth->where('year', $year)->each(function ($patient) use (&$patientCounts) {
            $patientCounts[$patient->month] = $patient->total;
        });


        $prev_value = '0';

        if ($year == date('Y')) {
            $currentMonth = date('n');
            for ($month = 1; $month <= $currentMonth; $month++) {

                foreach ($patientsPerMonth as $patient) {
                    if ($patient->year < $year) {
                        $prev_value = $patient->total;
                    }
                }
                if (!isset($patientCounts[$month])) {
                    $patientCounts[$month] = $prev_value;
                }
                $prev_value = $patientCounts[$month];
            }
        } else {
            for ($month = 1; $month <= 12; $month++) {
                if (!isset($patientCounts[$month])) {
                    $patientCounts[$month] = $prev_value;
                }
                $prev_value = $patientCounts[$month];
            }
        }

        ksort($patientCounts);

        $patientYears = Patient::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'asc')
            ->get();


        $result[] = [
            'data' => array_values($patientCounts),
            'label' => (string) $year,
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'patientYears' => $patientYears,
                'result' => $result,
                'patientCounts' => $patientsPerMonth,
            ]
        ]);
    }


    /**
     * Función para verificar si una identificación está disponible.
     *
     * @param string $identification La identificación a verificar
     * @param int $id El ID del paciente (opcional)
     * @return boolean true si la identificación está disponible, false en caso contrario
     */
    public function checkIdentificationIsAvailable($identification, $id = null)
    {

        $query = Patient::whereHas('person', function ($query) use ($identification) {
            $query->where('identification', $identification);
        });

        if ($id) {
            $query->where('id', '!=', $id);
        }

        $patient = $query->first();

        return json_encode(empty($patient));
    }

    /**
     * Función para verificar si un email está disponible.
     *
     * @param string $email El email a verificar
     * @param int $id El ID del paciente (opcional)
     * @return boolean true si el email está disponible, false en caso contrario
     */
    public function checkEmailIsAvailable($email, $id = null)
    {

        $query = Patient::whereHas('person', function ($query) use ($email) {
            $query->where('email', $email);
        });

        if ($id) {
            $query->where('id', '!=', $id);
        }

        $patient = $query->first();

        return json_encode(empty($patient));
    }
}
