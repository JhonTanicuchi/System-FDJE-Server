<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuppliesDelivery;
use App\Models\DetailSuppliesDelivery;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;

class SuppliesDeliveriesController extends Controller
{
    public function getSupplyDeliveries()
    {
        $suppliesDeliveries = SuppliesDelivery::where('archived', false)->get();

        $suppliesDeliveries->load('detail_supplies_delivery');
        $suppliesDeliveries->load('detail_supplies_delivery.supply');
        $suppliesDeliveries->load('patient');
        $suppliesDeliveries->load('patient.person');

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'supplies_deliveries' => $suppliesDeliveries
            ]
        ], 200);
    }

    public function getSupplyDeliveryById($id)
    {
        $suppliesDelivery = SuppliesDelivery::find($id);

        $suppliesDelivery->load('patient', 'patient.type', 'patient.person', 'patient.person.region', 'patient.medical_record', 'patient.medical_record.diabetes_type');
        $suppliesDelivery->load('detail_supplies_delivery');
        $suppliesDelivery->load('detail_supplies_delivery.supply');
        $suppliesDelivery->load('delivered_by');

        $patientId = $suppliesDelivery->patient;

        $firstDelivery = SuppliesDelivery::where('patient', $patientId)
            ->orderBy('created_at', 'asc')
            ->first();

        $lastDelivery = SuppliesDelivery::where('patient', $patientId)
            ->orderBy('created_at', 'desc')
            ->first();


        $totalSuppliesDeliveredToPatient = SuppliesDelivery::join('detail_supplies_deliveries', 'supplies_deliveries.id', '=', 'detail_supplies_deliveries.supplies_delivery')
            ->where('supplies_deliveries.patient', $patientId)
            ->selectRaw('SUM(detail_supplies_deliveries.quantity) as total_supplies')
            ->groupBy('supplies_deliveries.patient')
            ->first();

        $totalSuppliesDeliveredToPatientBySupply = SuppliesDelivery::join('detail_supplies_deliveries', 'supplies_deliveries.id', '=', 'detail_supplies_deliveries.supplies_delivery')
            ->join(
                'catalogs',
                'detail_supplies_deliveries.supply',
                '=',
                'catalogs.id'
            )
            ->where('supplies_deliveries.patient', $patientId)
            ->selectRaw('catalogs.value as supply, SUM(detail_supplies_deliveries.quantity) as total')
            ->groupBy('supplies_deliveries.patient', 'catalogs.value')
            ->get();

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'supplies_delivery' => $suppliesDelivery,
                'supplies_deliveries_history' => [
                    'first_delivery' => $firstDelivery->created_at,
                    'last_delivery' => $lastDelivery->created_at,
                    'total_supplies_delivered' => $totalSuppliesDeliveredToPatient->total_supplies,
                    'total_supplies_delivered_by_supply' => $totalSuppliesDeliveredToPatientBySupply
                ]
            ]
        ], 200);
    }

    public function getStatisticsSuppliesDeliveredByMonth($month)
    {
        $year = substr($month, 0, 4);
        $month = substr($month, 5, 2);

        $totalSuppliesDelivered = DetailSuppliesDelivery::with(['suppliesDelivery.patient.person'])
            ->whereHas('suppliesDelivery', function ($query) use ($month, $year) {
                $query->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);
            })
            ->selectRaw('SUM(quantity) as total_supplies')
            ->first();

        $totalSuppliesDeliveredBySupply = SuppliesDelivery::join('detail_supplies_deliveries', 'supplies_deliveries.id', '=', 'detail_supplies_deliveries.supplies_delivery')
            ->join('catalogs', 'detail_supplies_deliveries.supply', '=', 'catalogs.id')
            ->whereMonth('supplies_deliveries.created_at', $month)
            ->whereYear('supplies_deliveries.created_at', $year)
            ->selectRaw('catalogs.value, SUM(detail_supplies_deliveries.quantity) as total_supplies')
            ->groupByRaw('catalogs.value')
            ->get();

        $totalSuppliesDeliveredByRegion = SuppliesDelivery::join('detail_supplies_deliveries', 'supplies_deliveries.id', '=', 'detail_supplies_deliveries.supplies_delivery')
            ->join('catalogs as supply_catalog', 'detail_supplies_deliveries.supply', '=', 'supply_catalog.id')
            ->join('patients', 'supplies_deliveries.patient', '=', 'patients.id')
            ->join('people', 'patients.person', '=', 'people.id')
            ->join('catalogs as region_catalog', 'people.region', '=', 'region_catalog.id')
            ->whereMonth('supplies_deliveries.created_at', $month)
            ->whereYear('supplies_deliveries.created_at', $year)
            ->groupBy(DB::raw('supply_catalog.value, region_catalog.value'))
            ->selectRaw('supply_catalog.value as supply_value, region_catalog.value as region, SUM(detail_supplies_deliveries.quantity) as total_supplies')
            ->get();

        $results = [];
        foreach ($totalSuppliesDeliveredByRegion as $item) {
            if (!isset($results[$item->region])) {
                $results[$item->region] = [
                    'region' => $item->region,
                    'total' => 0,
                    'supplies' => [
                        [
                            'value' => $item->supply_value,
                            'total_supplies' => $item->total_supplies
                        ]
                    ]
                ];
            } else {
                $results[$item->region]['supplies'][] = [
                    'value' => $item->supply_value,
                    'total_supplies' => $item->total_supplies
                ];
            }
            $results[$item->region]['total'] += $item->total_supplies;
        }

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'total_supplies_delivered' => $totalSuppliesDelivered->total_supplies,
                'total_supplies_delivered_by_supply' => $totalSuppliesDeliveredBySupply,
                'total_supplies_delivered_by_region' => array_values($results)
            ]
        ], 200);
    }

    /**
     * Obtienen todos las entregas de insumos con sus detalles y sus pacientes apadrinados
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuppliesDeliveriesWithPatients()
    {

        $patients = Patient::where('archived', false)
            ->whereHas('type', function ($query) {
                $query->where('value', 'apadrinado');
            })
            ->get();

        $patients->load('person');
        $patients->load('type');
        $patients->load('medical_record');
        $patients->load('medical_record.diabetes_type');

        $currentMonth = Carbon::now()->month;
        $suppliesDeliveries = SuppliesDelivery::whereMonth('created_at', $currentMonth)->get();
        $suppliesDeliveries->load('detail_supplies_delivery');

        $patientsWithSuppliesDelivery = [];

        foreach ($patients as $patient) {
            $suppliesDeliveryByPatient = $suppliesDeliveries->where('patient', $patient->id)->last();
            array_push($patientsWithSuppliesDelivery, [
                'patient' => $patient,
                'supplies_delivery' => $suppliesDeliveryByPatient
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients_with_supplies_delivery' => $patientsWithSuppliesDelivery
            ]
        ]);
    }

    public function searchSuppliesDeliveriesByTerm($term = '')
    {
        $suppliesDeliveries = SuppliesDelivery::where('archived', false)
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

        $suppliesDeliveries->load('detail_supplies_delivery');
        $suppliesDeliveries->load('detail_supplies_delivery.supply');
        $suppliesDeliveries->load('patient');
        $suppliesDeliveries->load('patient.person');

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'supplies_deliveries' => $suppliesDeliveries
            ]
        ], 200);
    }

    public function searchSuppliesDeliveriesWithPatientsByTerm($term = '')
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
        $patients->load('medical_record');
        $patients->load('medical_record.diabetes_type');

        $currentMonth = Carbon::now()->month;
        $suppliesDeliveries = SuppliesDelivery::whereMonth('created_at', $currentMonth)->get();
        $suppliesDeliveries->load('detail_supplies_delivery');

        $patientsWithSuppliesDelivery = [];

        foreach ($patients as $patient) {
            $suppliesDeliveryByPatient = $suppliesDeliveries->where('patient', $patient->id)->last();
            array_push($patientsWithSuppliesDelivery, [
                'patient' => $patient,
                'supplies_delivery' => $suppliesDeliveryByPatient
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients_with_supplies_delivery' => $patientsWithSuppliesDelivery
            ]
        ]);
    }

    public function createSupplyDelivery(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $validatedData = $request->validate([
                    'patient' => 'required|array',
                    'patient.id' => 'required|integer',
                    'detail_supplies_delivery' => 'required|array',
                    'detail_supplies_delivery.*.supply' => 'required|array',
                    'detail_supplies_delivery.*.supply.id' => 'required|integer',
                    'detail_supplies_delivery.*.quantity' => 'required|integer',
                ]);

                $suppliesDelivery = SuppliesDelivery::create([
                    'patient' => $validatedData['patient']['id'],
                    'delivered_by' => auth()->user()->id
                ]);

                foreach ($validatedData['detail_supplies_delivery'] as $detailSuppliesDelivery) {
                    DetailSuppliesDelivery::create([
                        'supplies_delivery' => $suppliesDelivery->id,
                        'supply' => $detailSuppliesDelivery['supply']['id'],
                        'quantity' => $detailSuppliesDelivery['quantity'],
                    ]);
                }
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Entrega de insumos registrada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar la entrega de insumos: ' . $e->getMessage()
            ]);
        }
    }

    public function getArchivedSupplyDeliveries()
    {
        $suppliesDeliveries = SuppliesDelivery::where('archived', true)->get();
        $suppliesDeliveries->load('detail_supplies_delivery');
        $suppliesDeliveries->load('detail_supplies_delivery.supply');
        $suppliesDeliveries->load('patient');
        $suppliesDeliveries->load('patient.person', 'patient.person.identification_type');
        $suppliesDeliveries->load('archived_by');

        return response()->json([
            'status' => 'success',
            'data' => [
                'supplies_deliveries' => $suppliesDeliveries
            ]
        ]);
    }

    public function searchSuppliesDeliveriesArchivedByTerm($term = '')
    {
        $suppliesDeliveries = SuppliesDelivery::where('archived', true)
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

        $suppliesDeliveries->load('detail_supplies_delivery');
        $suppliesDeliveries->load('detail_supplies_delivery.supply');
        $suppliesDeliveries->load('patient');
        $suppliesDeliveries->load('patient.person');
        $suppliesDeliveries->load('archived_by');

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'supplies_deliveries' => $suppliesDeliveries
            ]
        ], 200);
    }

    public function archiveSupplyDelivery($id)
    {
        $suppliesDelivery = SuppliesDelivery::find($id);
        $suppliesDelivery->archived = true;
        $suppliesDelivery->archived_at = now();
        $suppliesDelivery->archived_by = auth()->user()->id;
        $suppliesDelivery->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Entrega de insumos archivada correctamente'
        ]);
    }

    public function restoreSupplyDelivery($id)
    {
        $suppliesDelivery = SuppliesDelivery::find($id);
        $suppliesDelivery->archived = false;
        $suppliesDelivery->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Entrega de insumos restaurada correctamente'
        ]);
    }

    public function deleteSupplyDelivery($id)
    {
        $suppliesDelivery = SuppliesDelivery::find($id);
        $suppliesDelivery->detail_supplies_delivery()->delete();
        $suppliesDelivery->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Entrega de insumos eliminada correctamente'
        ]);
    }
}
