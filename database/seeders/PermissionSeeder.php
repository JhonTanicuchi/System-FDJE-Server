<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Module;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'LEER_USUARIOS',
            'CREAR_USUARIOS',
            'ACTUALIZAR_USUARIOS',
            'ARCHIVAR_USUARIOS',
            'RESTAURAR_USUARIOS',
            'ELIMINAR_USUARIOS',
            'EXPORTAR_USUARIOS',
            'IMPORTAR_USUARIOS',
            'LEER_ROLES',
            'CREAR_ROLES',
            'ACTUALIZAR_ROLES',
            'ARCHIVAR_ROLES',
            'RESTAURAR_ROLES',
            'ELIMINAR_ROLES',
            'EXPORTAR_ROLES',
            'IMPORTAR_ROLES',
            'LEER_PERMISOS',
            'LEER_PACIENTES',
            'CREAR_PACIENTES',
            'ACTUALIZAR_PACIENTES',
            'ARCHIVAR_PACIENTES',
            'RESTAURAR_PACIENTES',
            'ELIMINAR_PACIENTES',
            'EXPORTAR_PACIENTES',
            'IMPORTAR_PACIENTES',
            'LEER_ENTREGAS_INSUMOS',
            'CREAR_ENTREGAS_INSUMOS',
            'ACTUALIZAR_ENTREGAS_INSUMOS',
            'ARCHIVAR_ENTREGAS_INSUMOS',
            'RESTAURAR_ENTREGAS_INSUMOS',
            'ELIMINAR_ENTREGAS_INSUMOS',
            'EXPORTAR_ENTREGAS_INSUMOS',
            'IMPORTAR_ENTREGAS_INSUMOS',
            'LEER_EXAMENES',
            'CREAR_EXAMENES',
            'ACTUALIZAR_EXAMENES',
            'ARCHIVAR_EXAMENES',
            'RESTAURAR_EXAMENES',
            'ELIMINAR_EXAMENES',
            'EXPORTAR_EXAMENES',
            'IMPORTAR_EXAMENES',
            'LEER_EXAMENES_HEMOGLOBINA',
            'CREAR_EXAMENES_HEMOGLOBINA',
            'ACTUALIZAR_EXAMENES_HEMOGLOBINA',
            'ARCHIVAR_EXAMENES_HEMOGLOBINA',
            'RESTAURAR_EXAMENES_HEMOGLOBINA',
            'ELIMINAR_EXAMENES_HEMOGLOBINA',
            'EXPORTAR_EXAMENES_HEMOGLOBINA',
            'IMPORTAR_EXAMENES_HEMOGLOBINA',
            'LEER_CATALOGOS',
            'CREAR_CATALOGOS',
            'ACTUALIZAR_CATALOGOS',
            'ELIMINAR_CATALOGOS',
            'EXPORTAR_CATALOGOS',
            'IMPORTAR_CATALOGOS',
            'PROGRAMAR'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
