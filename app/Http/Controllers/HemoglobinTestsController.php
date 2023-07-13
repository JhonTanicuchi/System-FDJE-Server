<?php

namespace App\Http\Controllers;

use App\Models\HemoglobinTest;
use App\Models\Patient;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class HemoglobinTestsController extends Controller
{
    public function getHemoglobinTests()
    {
        $hemoglobinTests = HemoglobinTest::where('archived', false)->get();
        $hemoglobinTests->load('patient');
        $hemoglobinTests->load('patient.person');

        return response()->json([
            'status' => 'success',
            'data' => [
                'hemoglobin_tests' => $hemoglobinTests
            ]
        ]);
    }

    public function getHemoglobinTestById($id)
    {
        $hemoglobinTest = HemoglobinTest::find($id);
        $hemoglobinTest->load('patient');
        $hemoglobinTest->load('state');

        return response()->json([
            'status' => 'success',
            'data' => [
                'hemoglobin_test' => $hemoglobinTest
            ]
        ]);
    }

    public function getHemoglobinTestLast($id)
    {
        $patient = Patient::find($id);
        $hemoglobinTestLast = $patient->hemoglobinTest()->orderBy('created_at', 'desc')->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'hemoglobinTest_last' => $hemoglobinTestLast
            ]
        ]);
    }

    public function getHemoglobinTestsWithPatients()
    {
        $patients = Patient::where('archived', false)->whereHas('type', function ($query) {
            $query->where('value', 'apadrinado');
        })->get();

        $patients->load('person');
        $patients->load('type');

        $hemoglobinTests = HemoglobinTest::all();

        $patientsWithHemoglobinTests = [];

        foreach ($patients as $patient) {
            $hemoglobinTestByPatient = $hemoglobinTests->where('patient', $patient->id)->last();
            array_push($patientsWithHemoglobinTests, [
                'patient' => $patient,
                'hemoglobin_test' => $hemoglobinTestByPatient
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients_with_hemoglobin_tests' => $patientsWithHemoglobinTests
            ]
        ]);
    }


    public function searchHemoglobinTestsByTerm($term = '')
    {
        $hemoglobinTests = HemoglobinTest::where('archived', false)
            ->where(function ($query) use ($term) {
                $query->orWhereHas('patient.person', function ($query) use ($term) {
                    $query->where('names', 'like', '%' . $term . '%')
                        ->orWhere('last_names', 'like', '%' . $term . '%')
                        ->orWhere('identification', 'like', '%' . $term . '%')
                        ->orWhereRaw("concat(names, ' ', last_names) like ?", ['%' . $term . '%']);
                })
                    ->orWhere('delivered', 'like', '%' . $term . '%');
            })
            ->get();

        $hemoglobinTests->load('patient');
        $hemoglobinTests->load('patient.person');

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'hemoglobin_tests' => $hemoglobinTests
            ]
        ], 200);
    }

    public function searchHemoglobinTestsWithPatientsByTerm($term = '')
    {
        $patients = Patient::where('archived', false)
            ->whereHas('type', function ($query) {
                $query->where('value', 'apadrinado');
            })
            ->where(function ($query) use ($term) {
                $query->orWhereHas('person', function ($query) use ($term) {
                    $query->where('names', 'like', '%' . $term . '%')
                        ->orWhere('last_names', 'like', '%' . $term . '%')
                        ->orWhere('identification', 'like', '%' . $term . '%')
                        ->orWhereRaw("concat(names, ' ', last_names) like ?", ['%' . $term . '%']);
                });
            })
            ->get();

        $patients->load('person');
        $patients->load('type');

        $hemoglobinTests = HemoglobinTest::all();

        $patientsWithHemoglobinTests = [];

        foreach ($patients as $patient) {
            $hemoglobinTestByPatient = $hemoglobinTests->where('patient', $patient->id)->last();
            array_push($patientsWithHemoglobinTests, [
                'patient' => $patient,
                'hemoglobin_test' => $hemoglobinTestByPatient
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients_with_hemoglobin_tests' => $patientsWithHemoglobinTests
            ]
        ]);
    }

    public function createHemoglobinTest(Request $request)
    {
        $request->validate([
            'patient' => 'required|array',
            'hb1ac_result' => 'required|numeric',
            'hb1ac_date' => 'required|date',
            'endocrinologist_date' => 'required|date',
            'size' => 'required|numeric',
            'weight' => 'required|numeric',
            'state' => 'required|integer',
        ]);

        try {
            DB::transaction(
                function () use ($request) {

                    HemoglobinTest::create(array_merge(
                        $request->all(),
                        [
                            'patient' => $request->patient['id'],
                            'hb1ac_date' => date('Y-m-d', strtotime($request->hb1ac_date)),
                            'endocrinologist_date' => date('Y-m-d', strtotime($request->endocrinologist_date))
                        ]
                    ));

                    $patient = Patient::find($request->patient['id']);
                    $medicalRecord = MedicalRecord::find($patient->medical_record);

                    $medicalRecord->weight = $request->weight;
                    $medicalRecord->size = $request->size;

                    $medicalRecord->save();
                }
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Registro de hemoglobina creado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateHemoglobinTest(Request $request, $id)
    {
        $request->validate([
            'patient' => 'required|array',
            'hb1ac_result' => 'required',
            'hb1ac_date' => 'required|date',
            'endocrinologist_date' => 'required|date',
            'size' => 'required',
            'weight' => 'required',
        ]);

        try {
            DB::transaction(
                function () use ($request, $id) {
                    $hemoglobinTest = HemoglobinTest::find($id);
                    $hemoglobinTest->update(array_merge(
                        $request->all(),
                        [
                            'patient' => $request->patient['id'],
                            'hb1ac_date' => date('Y-m-d', strtotime($request->hb1ac_date)),
                            'endocrinologist_date' => date('Y-m-d', strtotime($request->endocrinologist_date))
                        ]
                    ));

                    $patient = Patient::find($request->patient['id']);
                    $medicalRecord = MedicalRecord::find($patient->medical_record);

                    $medicalRecord->weight = $request->weight;
                    $medicalRecord->size = $request->weight;

                    $medicalRecord->save();
                }
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Registro de hemoglobina actualizado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el registro de hemoglobina: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getArchivedHemoglobinTests()
    {
        $hemoglobinTests = HemoglobinTest::where('archived', true)->get();
        $hemoglobinTests->load('patient');
        $hemoglobinTests->load('patient.person', 'patient.person.identification_type');
        $hemoglobinTests->load('archived_by');

        return response()->json([
            'status' => 'success',
            'data' => [
                'hemoglobin_tests' => $hemoglobinTests
            ]
        ]);
    }

    public function searchHemoglobinTestsArchivedByTerm($term = '')
    {
        $hemoglobinTests = HemoglobinTest::where('archived', true)
            ->where(function ($query) use ($term) {
                $query->orWhereHas('patient.person', function ($query) use ($term) {
                    $query->where('names', 'like', '%' . $term . '%')
                        ->orWhere('last_names', 'like', '%' . $term . '%')
                        ->orWhere('identification', 'like', '%' . $term . '%')
                        ->orWhereRaw("concat(names, ' ', last_names) like ?", ['%' . $term . '%']);
                })
                    ->orWhere('delivered', 'like', '%' . $term . '%');
            })
            ->get();

        $hemoglobinTests->load('patient');
        $hemoglobinTests->load('patient.person');

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'hemoglobin_tests' => $hemoglobinTests
            ]
        ], 200);
    }

    public function archiveHemoglobinTest($id)
    {
        $hemoglobinTest = HemoglobinTest::find($id);
        $hemoglobinTest->archived = true;
        $hemoglobinTest->archived_at = now();
        $hemoglobinTest->archived_by = auth()->user()->id;
        $hemoglobinTest->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Examen de hemoglobina archivado correctamente'
        ]);
    }

    public function restoreHemoglobinTest($id)
    {
        $hemoglobinTest = HemoglobinTest::find($id);
        $hemoglobinTest->archived = false;
        $hemoglobinTest->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Examen de hemoglobina restaurado correctamente'
        ]);
    }

    public function deleteHemoglobinTest($id)
    {
        $hemoglobinTest = HemoglobinTest::find($id);
        $hemoglobinTest->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Examen de hemoglobina eliminado correctamente'
        ]);
    }
}
