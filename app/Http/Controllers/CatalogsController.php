<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Clase para controlar las acciones relacionadas con los catálogos.
 */
class CatalogsController extends Controller
{

    /**
     * Obtiene todos los catálogos.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCatalogs()
    {
        $catalogs = Catalog::all();

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'catalogs' => $catalogs
            ]
        ], 200);
    }

    /**
     * Obtiene todos los catálogos de un tipo específico.
     *
     * @param  string  $type
     * @return \Illuminate\Http\Response
     */
    public function getCatalogsByType($type)
    {
        $types = explode(',', $type);

        $catalogs = Catalog::whereIn('type', $types)->get();

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'catalogs' => $catalogs
            ]
        ], 200);
    }

    /**
     * Busca catálogos por un término específico dentro de un tipo de catálogo.
     *
     * @param  string  $type
     * @param  string  $term
     * @return \Illuminate\Http\Response
     */
    public function searchCatalogByTerm($type, $term = '')
    {
        $catalogs = Catalog::where('type', $type)
            ->where('value', 'like', '%' . $term . '%')
            ->get();

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'catalogs' => $catalogs
            ]
        ], 200);
    }

    /**
     * Obtiene un catálogo específico por su ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCatalogById($id)
    {
        try {
            $catalog = Catalog::findOrFail($id);

            return response()->json($catalog);
        } catch (ModelNotFoundException $ex) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Catalogo no encontrado'
            ], 200);
        }
    }

    /**
     * Obtiene un catálogo específico por su tipo y valor.
     *
     * @param  string  $type
     * @param  string  $value
     * @return \Illuminate\Http\Response
     */
    public function getCatalogByTypeAndValue($type, $value)
    {
        $catalog = Catalog::where('type', $type)->where('value', $value)->first();

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'catalog' => $catalog
            ]
        ], 200);
    }

    /**
     * Crea un nuevo catálogo en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createCatalog(Request $request)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        try {
            $catalog = Catalog::create($request->all());

            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'catalog' => $catalog
                ],
                'message' => 'Catalogo creado correctamente',
            ], 201);
        } catch (\Exception $ex) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al crear catalogo'
            ], 500);
        }
    }

    /**
     * Actualiza un catálogo en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCatalog(Request $request, $id)
    {
        try {
            $catalog = Catalog::findOrFail($id);
            $catalog->type = $request->type;
            $catalog->value = $request->value;
            $catalog->save();

            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'catalog' => $catalog
                ],
                'message' => 'Catalogo actualizado correctamente',
            ], 200);
        } catch (ModelNotFoundException $ex) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Catalogo no encontrado'
            ], 500);
        } catch (\Exception $ex) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al actualizar catalogo'
            ], 500);
        }
    }

    /**
     * Elimina un catálogo de la base de datos.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteCatalog($id)
    {
        try {
            $catalog = Catalog::findOrFail($id);
            $catalog->delete();

            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'catalog' => $catalog
                ],
                'message' => 'Catalogo eliminado correctamente'
            ], 200);
        } catch (ModelNotFoundException $ex) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Catalogo no encontrado'
            ], 500);
        } catch (\Illuminate\Database\QueryException $ex) {
            if ($ex->getCode() == 23000) {
                return new JsonResponse([
                    'status' => 'alert',
                    'message' => 'No se puede eliminar el catálogo porque está en uso'
                ], 500);
            } else {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Error al eliminar catalogo'
                ], 500);
            }
        } catch (\Exception $ex) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al eliminar catalogo'
            ], 500);
        }
    }

    /**
     * Función para verificar si un valor de un catálogo está disponible.
     *
     * @param string $type Tipo de catalogo
     * @param string $value valor del catalogo
     * @param int $id El ID del catálogo (opcional)
     * @return boolean true si el valor del catálogo está disponible, false en caso contrario
     */
    public function checkCatalogValueIsAvailable($type, $value, $id = null)
    {
        $query = Catalog::where('type', $type)->where('value', $value);

        if ($id) {
            $query->where('id', '!=', $id);
        }
        $catalog = $query->first();
        return json_encode($catalog == null);
    }
}
