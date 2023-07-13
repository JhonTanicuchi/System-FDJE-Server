<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Catalog;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $catalogs = [
            ['type' => 'estados-examen', 'value' => 'Urgente visita al médico'],
            ['type' => 'estados-examen', 'value' => 'Salio del programa'],
            ['type' => 'estados-examen', 'value' => 'Caso nuevo'],
            ['type' => 'estados-examen', 'value' => 'Sin novedad'],
            ['type' => 'insumos', 'value' => 'Jeringa para insulina'],
            ['type' => 'regiones', 'value' => 'UIO SIERRA ZONA'],
            ['type' => 'regiones', 'value' => 'PROV. SIERRA ZONA'],
            ['type' => 'regiones', 'value' => 'GUAYAS ZONA'],
            ['type' => 'regiones', 'value' => 'STA. ELENA (NORTE) ZONA'],
            ['type' => 'regiones', 'value' => 'STA. ELENA SUR ZONA'],
            ['type' => 'regiones', 'value' => 'ANCONCITO STA ELENA'],
            ['type' => 'tiempo-diagnostico', 'value' => 'De 6 meses a 2 años'],
            ['type' => 'tiempo-diagnostico', 'value' => 'De 1 mes a 6 meses'],
            ['type' => 'tiempo-diagnostico', 'value' => 'Mas de 2 años'],
            ['type' => 'tipos-diabetes', 'value' => 'Tipo 1'],
            ['type' => 'tipos-diabetes', 'value' => 'Tipo 2'],
            ['type' => 'tipos-diabetes', 'value' => 'Gestacional'],
            ['type' => 'tipos-diabetes', 'value' => 'Otra (Mody, Lada, Neonatall, etc)'],
            ['type' => 'tipos-hospital', 'value' => 'Público'],
            ['type' => 'tipos-hospital', 'value' => 'Privado'],
            ['type' => 'tipos-hospital', 'value' => 'IESS'],
            ['type' => 'tipos-hospital', 'value' => 'ISSFA'],
            ['type' => 'tipos-hospital', 'value' => 'ISSPOL'],
            ['type' => 'tipos-identificacion', 'value' => 'Cédula'],
            ['type' => 'tipos-identificacion', 'value' => 'Pasaporte'],
            ['type' => 'tipos-identificacion', 'value' => 'Carnét de discapaciadad'],
            ['type' => 'tipos-informacion-ayuda', 'value' => 'Educación en diabetes'],
            ['type' => 'tipos-informacion-ayuda', 'value' => 'Apoyo de padres a padres'],
            ['type' => 'tipos-informacion-ayuda', 'value' => 'Acceso a insumos más económicos'],
            ['type' => 'tipos-informacion-ayuda', 'value' => 'Talleres de capacitación'],
            ['type' => 'tipos-informacion-ayuda', 'value' => 'Desea ayuda para insumos'],
            ['type' => 'tipos-insulina-basal', 'value' => 'LANTUS'],
            ['type' => 'tipos-insulina-basal', 'value' => 'LEVEMIR'],
            ['type' => 'tipos-insulina-basal', 'value' => 'NOVOLIN N/HUMILIN N'],
            ['type' => 'tipos-insulina-basal', 'value' => 'TOUJEO'],
            ['type' => 'tipos-insulina-basal', 'value' => 'TRESIBA'],
            ['type' => 'tipos-insulina-basal', 'value' => 'NINGUNA'],
            ['type' => 'tipos-insulina-prandial', 'value' => 'APIDRA'],
            ['type' => 'tipos-insulina-prandial', 'value' => 'HUMALOG'],
            ['type' => 'tipos-insulina-prandial', 'value' => 'NOVORAPID'],
            ['type' => 'tipos-insulina-prandial', 'value' => 'NOVOLIN R/HUMILIN R'],
            ['type' => 'tipos-insulina-prandial', 'value' => 'NINGUNA'],
            ['type' => 'tipos-nacionalidad', 'value' => 'Ecuatoriana'],
            ['type' => 'tipos-nacionalidad', 'value' => 'Extranjera'],
            ['type' => 'tipos-paciente', 'value' => 'Base DM1'],
            ['type' => 'tipos-paciente', 'value' => 'Debutante'],
            ['type' => 'tipos-paciente', 'value' => 'Apadrinado'],
            ['type' => 'tipos-paciente', 'value' => 'Ayuda Humanitaria'],
            ['type' => 'tipos-paciente', 'value' => 'Equipo Técnico'],
            ['type' => 'tipos-paciente', 'value' => 'Nuevo'],
        ];

        Catalog::insert($catalogs);
    }
}
