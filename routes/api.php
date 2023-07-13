<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\EmailsController;
use App\Http\Controllers\CatalogsController;
use App\Http\Controllers\PatientsController;
use App\Http\Controllers\SuppliesDeliveriesController;
use App\Http\Controllers\TestsController;
use App\Http\Controllers\HemoglobinTestsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar rutas de API para tu aplicación. Estas
| rutas son cargadas por el RouteServiceProvider dentro de un grupo que
| tiene el middleware "api" asignado. Disfruta construyendo tu API!
|
*/

// Auth Routes
Route::prefix('auth')->group(function () {
    //ruta para loguear usuario
    Route::post('/login', [AuthController::class, 'login']);
    //Rutas protegidas
    Route::middleware('authentication')->group(function () {
        //ruta para obtener usuario autenticado
        Route::get('/profile', [AuthController::class, 'getProfile']);
        //ruta para cerrar sesión
        Route::delete('/logout', [AuthController::class, 'logout']);
    });

    //ruta para refrescar token
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

//Rutas abiertas
Route::prefix('catalogs')->group(function () {
    //ruta para obtener todos los catalogos por tipo
    Route::get('/{type}', [CatalogsController::class, 'getCatalogsByType']);
    //ruta para obtener catalogo por id
    Route::get('id/{id}', [CatalogsController::class, 'getCatalogById']);
    //ruta para obtener catalogo por tipo y valor
    Route::get('/type/{type}/value/{value}', [CatalogsController::class, 'getCatalogByTypeAndValue']);
});

Route::prefix('patients')->group(function () {
    //ruta para crear paciente
    Route::post('/create', [PatientsController::class, 'createPatient']);
    //ruta para validar si la identificación está disponible
    Route::get('/validate/identification/{identification}/{id?}', [PatientsController::class, 'checkIdentificationIsAvailable']);
    //ruta para validar si el correo está disponible
    Route::get('/validate/email/{email}/{id?}', [PatientsController::class, 'checkEmailIsAvailable']);
});

//Rutas protegidas
Route::middleware('authentication')->group(function () {
    // Catalogs
    Route::prefix('catalogs')->group(function () {
        //ruta para obtener todos los catalogos
        Route::get('/', [CatalogsController::class, 'getCatalogs'])->middleware('permission:LEER_CATALOGOS');
        //ruta para obtener catalogo por tipo y valor
        Route::get('/search/{type}/{term?}', [CatalogsController::class, 'searchCatalogByTerm'])->middleware('permission:LEER_CATALOGOS');
        //ruta para crear catalogo
        Route::post('/create', [CatalogsController::class, 'createCatalog'])->middleware('permission:CREAR_CATALOGOS');
        //ruta para actualizar catalogo
        Route::put('/update/{id}', [CatalogsController::class, 'updateCatalog'])->middleware('permission:ACTUALIZAR_CATALOGOS');
        //ruta para eliminar catalogo
        Route::delete('/delete/{id}', [CatalogsController::class, 'deleteCatalog'])->middleware('permission:ELIMINAR_CATALOGOS');
        //ruta para validar si el valor está disponible
        Route::get('/validate/value/{type}/{value}/{id?}', [CatalogsController::class, 'checkCatalogValueIsAvailable']);
    });

    //Users
    Route::prefix('users')->group(function () {
        //ruta para obtener todos los usuarios
        Route::get('/', [UsersController::class, 'getUsers'])->middleware('permission:LEER_USUARIOS');
        //ruta para obtener usuario por id
        Route::get('/{id}', [UsersController::class, 'getUserById'])->middleware('permission:LEER_USUARIOS');
        //ruta para obtener usuarios por termino de búsqueda
        Route::get('/search/term/{term?}', [UsersController::class, 'searchUsuariosByTerm'])->middleware('permission:LEER_USUARIOS');
        //ruta para crear usuario
        Route::post('/create', [UsersController::class, 'createUser'])->middleware('permission:CREAR_USUARIOS');
        //ruta para enviar correo con credenciales
        Route::post('/email/send-credentials', [EmailsController::class, 'sendEmail'])->middleware('permission:CREAR_USUARIOS');
        //ruta para actualizar contraseña
        Route::put('/update-password/{id}', [UsersController::class, 'updatePassword'])->middleware('permission:ACTUALIZAR_USUARIOS');
        //ruta para actualizar usuario
        Route::put('/update/{id}', [UsersController::class, 'updateUser'])->middleware('permission:ACTUALIZAR_USUARIOS');
        //ruta para obtener todos los usuarios archivados
        Route::get('/archived/list', [UsersController::class, 'getArchivedUsers'])->middleware('permission:LEER_USUARIOS');
        //ruta para obtener usuarios archivados por termino de búsqueda
        Route::get('/archived/search/term/{term?}', [UsersController::class, 'searchUsuariosArchivedByTerm'])->middleware('permission:LEER_USUARIOS');
        //ruta para archivar usuario
        Route::put('/archive/{id}', [UsersController::class, 'archiveUser'])->middleware('permission:ARCHIVAR_USUARIOS');
        //ruta para restaurar usuario
        Route::put('/restore/{id}', [UsersController::class, 'restoreUser'])->middleware('permission:RESTAURAR_USUARIOS');
        //ruta para eliminar usuario
        Route::delete('/delete/{id}', [UsersController::class, 'deleteUser'])->middleware('permission:ELIMINAR_USUARIOS');
        //ruta para validar si la identificación está disponible
        Route::get('/validate/identification/{identification}/{id?}', [UsersController::class, 'checkIdentificationIsAvailable']);
        //ruta para validar si el correo está disponible
        Route::get('/validate/email/{email}/{id?}', [UsersController::class, 'checkEmailIsAvailable']);
        //ruta para validar si la contraseña es igual a la actual
        Route::get('/validate/password/{password}/{id}', [UsersController::class, 'checkPasswordIsEqual']);
    });

    //Roles
    Route::prefix('roles')->group(function () {
        //ruta para obtener todos los roles
        Route::get('/', [RolesController::class, 'getRoles'])->middleware('permission:LEER_ROLES');
        //ruta para obtener rol por id
        Route::get('/{id}', [RolesController::class, 'getRoleById'])->middleware('permission:LEER_ROLES');
        //ruta para obtener roles por termino de búsqueda
        Route::get('/search/term/{term?}', [RolesController::class, 'searchRolesByTerm'])->middleware('permission:LEER_ROLES');
        //ruta para crear rol
        Route::post('/create', [RolesController::class, 'createRole'])->middleware('permission:CREAR_ROLES');
        //ruta para actualizar rol
        Route::put('/update/{id}', [RolesController::class, 'updateRole'])->middleware('permission:ACTUALIZAR_ROLES');
        //ruta para obtener todos los roles archivados
        Route::get('/archived/list', [RolesController::class, 'getArchivedRoles'])->middleware('permission:LEER_ROLES');
        //ruta para obtener roles archivados por termino de búsqueda
        Route::get('/archived/search/term/{term?}', [RolesController::class, 'searchRolesArchivedByTerm'])->middleware('permission:LEER_ROLES');
        //ruta para archivar rol
        Route::put('/archive/{id}', [RolesController::class, 'archiveRole'])->middleware('permission:ARCHIVAR_ROLES');
        //ruta para restaurar rol
        Route::put('/restore/{id}', [RolesController::class, 'restoreRole'])->middleware('permission:RESTAURAR_ROLES');
        //ruta para eliminar rol
        Route::delete('/delete/{id}', [RolesController::class, 'deleteRole'])->middleware('permission:ELIMINAR_ROLES');
        //ruta para validar si el nombre está disponible
        Route::get('/validate/name/{name}/{id?}', [RolesController::class, 'checkRolNameIsAvailable']);
    });

    //Permissions
    Route::prefix('permissions')->group(function () {
        //ruta para obtener todos los permisos
        Route::get('/', [PermissionsController::class, 'getPermissions'])->middleware('permission:LEER_PERMISOS');
        //ruta para obtener permisos por id de rol
        Route::get('/role/{value}', [PermissionsController::class, 'getPermissionsByRole'])->middleware('permission:LEER_PERMISOS');
    });

    //Patients
    Route::prefix('patients')->group(function () {
        //ruta para obtener todos los pacientes
        Route::get('/', [PatientsController::class, 'getPatients'])->middleware('permission:LEER_PACIENTES');
        //ruta para obtener paciente por id
        Route::get('/{id}', [PatientsController::class, 'getPatientById'])->middleware('permission:LEER_PACIENTES');
        //ruta para obtener paciente por termino de búsqueda
        Route::get('/search/term/{term?}', [PatientsController::class, 'searchPatientsByTerm'])->middleware('permission:LEER_PACIENTES');
        //ruta para actualizar paciente
        Route::put('/update/{id}', [PatientsController::class, 'updatePatient'])->middleware('permission:ACTUALIZAR_PACIENTES');
        //ruta para obtener todos los pacientes archivados
        Route::get('/archived/list', [PatientsController::class, 'getArchivedPatients'])->middleware('permission:LEER_PACIENTES');
        //ruta para obtener pacientes archivados por termino de búsqueda
        Route::get('/archived/search/term/{term?}', [PatientsController::class, 'searchPatientsArchivedByTerm'])->middleware('permission:LEER_PACIENTES');
        //ruta para archivar paciente
        Route::put('/archive/{id}', [PatientsController::class, 'archivePatient'])->middleware('permission:ARCHIVAR_PACIENTES');
        //ruta para restaurar paciente
        Route::put('/restore/{id}', [PatientsController::class, 'restorePatient'])->middleware('permission:RESTAURAR_PACIENTES');
        //ruta para eliminar paciente
        Route::delete('/delete/{id}', [PatientsController::class, 'deletePatient'])->middleware('permission:ELIMINAR_PACIENTES');
        //ruta para obtener todos los pacientes en relación mes año
        Route::get('/chart/month-year/{year}', [PatientsController::class, 'getPatientsPerMonthPerYear'])->middleware('permission:LEER_PACIENTES');
    });

    //Supplies deliveries
    Route::prefix('supplies-deliveries')->group(function () {
        //ruta para obtener las estadísticas de los insumos y su conteo general por mes
        Route::get('/statistics/{month}', [SuppliesDeliveriesController::class, 'getStatisticsSuppliesDeliveredByMonth'])->middleware('permission:LEER_ENTREGAS_INSUMOS|LEER_PACIENTES');
        //ruta para obtener todas las entregas de insumos
        Route::get('/', [SuppliesDeliveriesController::class, 'getSupplyDeliveries'])->middleware(['permission:LEER_PACIENTES|LEER_ENTREGAS_INSUMOS']);
        //ruta para obtener entrega de insumos por id
        Route::get('/{id}', [SuppliesDeliveriesController::class, 'getSupplyDeliveryById'])->middleware('permission:LEER_ENTREGAS_INSUMOS|LEER_PACIENTES');
        //ruta para obtener todos los pacientes anidados con entregas de insumos
        Route::get('/patients/list', [SuppliesDeliveriesController::class, 'getSuppliesDeliveriesWithPatients'])->middleware('permission:LEER_PACIENTES|LEER_ENTREGAS_INSUMOS');
        //ruta para obtener todas las entregas de insumos por termino
        Route::get('/search/term/{term?}', [SuppliesDeliveriesController::class, 'searchSuppliesDeliveriesByTerm'])->middleware('permission:LEER_PACIENTES|LEER_ENTREGAS_INSUMOS');
        //ruta para obtener todos los pacientes anidados con entregas de insumos por termino
        Route::get('/patients/search/term/{term?}', [SuppliesDeliveriesController::class, 'searchSuppliesDeliveriesWithPatientsByTerm'])->middleware('permission:LEER_PACIENTES|LEER_ENTREGAS_INSUMOS');
        //ruta para crear entrega de insumos
        Route::post('/create', [SuppliesDeliveriesController::class, 'createSupplyDelivery'])->middleware('permission:CREAR_ENTREGAS_INSUMOS');
        //ruta para actualizar entrega de insumos
        Route::put('/update/{id}', [SuppliesDeliveriesController::class, 'updateSupplyDelivery'])->middleware('permission:ACTUALIZAR_ENTREGAS_INSUMOS');
        //ruta para obtener todas las entregas de insumos archivadas
        Route::get('/archived/list', [SuppliesDeliveriesController::class, 'getArchivedSupplyDeliveries'])->middleware('permission:LEER_ENTREGAS_INSUMOS');
        //ruta para obtener entregas de insumos archivadas por termino de búsqueda
        Route::get('/archived/search/term/{term?}', [SuppliesDeliveriesController::class, 'searchSuppliesDeliveriesArchivedByTerm'])->middleware('permission:LEER_ENTREGAS_INSUMOS');
        //ruta para archivar entrega de insumos
        Route::put('/archive/{id}', [SuppliesDeliveriesController::class, 'archiveSupplyDelivery'])->middleware('permission:ARCHIVAR_ENTREGAS_INSUMOS');
        //ruta para restaurar entrega de insumos
        Route::put('/restore/{id}', [SuppliesDeliveriesController::class, 'restoreSupplyDelivery'])->middleware('permission:RESTAURAR_ENTREGAS_INSUMOS');
        //ruta para eliminar entrega de insumos
        Route::delete('/delete/{id}', [SuppliesDeliveriesController::class, 'deleteSupplyDelivery'])->middleware('permission:ELIMINAR_ENTREGAS_INSUMOS');
    });

    //hemoglobin tests
    Route::prefix('hemoglobin-tests')->group(function () {
        //ruta para obtener todos los pruebas de hemoglobina
        Route::get('/', [HemoglobinTestsController::class, 'getHemoglobinTests'])->middleware('permission:LEER_EXAMENES_HEMOGLOBINA');
        //ruta para obtener prueba de hemoglobina por id
        Route::get('/{id}', [HemoglobinTestsController::class, 'getHemoglobinTestById'])->middleware('permission:LEER_EXAMENES_HEMOGLOBINA');
        //ruta para obtener todos los pacientes anidados con pruebas de hemoglobina
        Route::get('/patients/list', [HemoglobinTestsController::class, 'getHemoglobinTestsWithPatients'])->middleware('permission:LEER_EXAMENES_HEMOGLOBINA');
        //ruta para obtener todas las entregas de insumos por termino
        Route::get('/search/term/{term?}', [HemoglobinTestsController::class, 'searchHemoglobinTestsByTerm'])->middleware('permission:LEER_PACIENTES|LEER_EXAMENES_HEMOGLOBINA');
        //ruta para obtener todos los pacientes anidados con entregas de insumos por termino
        Route::get('/patients/search/term/{term?}', [HemoglobinTestsController::class, 'searchHemoglobinTestsWithPatientsByTerm'])->middleware('permission:LEER_PACIENTES|LEER_EXAMENES_HEMOGLOBINA');
        //ruta para obtener el ultimo examen del paciente
        Route::get('/patient/{id}/hemoglobin_test_last', [HemoglobinTestsController::class, 'getHemoglobinTestLast'])->middleware('permission:LEER_PACIENTES|LEER_EXAMENES_HEMOGLOBINA');
        //ruta para crear prueba de hemoglobina
        Route::post('/create', [HemoglobinTestsController::class, 'createHemoglobinTest'])->middleware('permission:CREAR_EXAMENES_HEMOGLOBINA');
        //ruta para actualizar prueba de hemoglobina
        Route::put('/update/{id}', [HemoglobinTestsController::class, 'updateHemoglobinTest'])->middleware('permission:ACTUALIZAR_EXAMENES_HEMOGLOBINA');
        //ruta para obtener todas las pruebas de hemoglobina archivadas
        Route::get('/archived/list', [HemoglobinTestsController::class, 'getArchivedHemoglobinTests'])->middleware('permission:LEER_EXAMENES_HEMOGLOBINA');
        //ruta para obtener pruebas de hemoglobina archivadas por termino de búsqueda
        Route::get('/archived/search/term/{term?}', [HemoglobinTestsController::class, 'searchHemoglobinTestsArchivedByTerm'])->middleware('permission:LEER_EXAMENES_HEMOGLOBINA');
        //ruta para archivar prueba de hemoglobina
        Route::put('/archive/{id}', [HemoglobinTestsController::class, 'archiveHemoglobinTest'])->middleware('permission:ARCHIVAR_EXAMENES_HEMOGLOBINA');
        //ruta para restaurar prueba de hemoglobina
        Route::put('/restore/{id}', [HemoglobinTestsController::class, 'restoreHemoglobinTest'])->middleware('permission:RESTAURAR_EXAMENES_HEMOGLOBINA');
        //ruta para eliminar prueba de hemoglobina
        Route::delete('/delete/{id}', [HemoglobinTestsController::class, 'deleteHemoglobinTest'])->middleware('permission:ELIMINAR_EXAMENES_HEMOGLOBINA');
    });
    /* ELIMINAR_EXAMENES_HEMOGLOBINA */
    //glycemic tests
    Route::prefix('tests')->group(function () {
        //ruta para obtener todos los pruebas de glucosa
        Route::get('/', [TestsController::class, 'getTests'])->middleware('permission:LEER_EXAMENES');
        //ruta para obtener prueba de glucosa por id
        Route::get('/{id}', [TestsController::class, 'getTestById'])->middleware('permission:LEER_EXAMENES');
        //ruta para obtener todos los pacientes anidados con pruebas de hemoglobina
        Route::get('/patients/list', [TestsController::class, 'getTestsWithPatients'])->middleware('permission:LEER_PACIENTES|LEER_EXAMENES');
        //ruta para obtener todas las entregas de insumos por termino
        Route::get('/search/term/{term?}', [TestsController::class, 'searchTestsByTerm'])->middleware('permission:LEER_EXAMENES');
        //ruta para obtener todos los pacientes anidados con entregas de insumos por termino
        Route::get('/patients/search/term/{term?}', [TestsController::class, 'searchTestsWithPatientsByTerm'])->middleware('permission:LEER_PACIENTES|LEER_EXAMENES');
        //ruta para obtener el ultimo examen del paciente
        Route::get('/patient/{id}/test_last', [TestsController::class, 'getTestLast'])->middleware('permission:LEER_PACIENTES|LEER_EXAMENES');
        //ruta para crear prueba de glucosa
        Route::post('/create', [TestsController::class, 'createTest'])->middleware('permission:CREAR_EXAMENES');
        //ruta para actualizar prueba de glucosa
        Route::put('/update/{id}', [TestsController::class, 'updateTest'])->middleware('permission:ACTUALIZAR_EXAMENES');
        //ruta para obtener todos los examens archivados
        Route::get('/archived/list', [TestsController::class, 'getArchivedTests'])->middleware('permission:LEER_EXAMENES');
        //ruta para obtener pruebas de hemoglobina archivadas por termino de búsqueda
        Route::get('/archived/search/term/{term?}', [TestsController::class, 'searchTestsArchivedByTerm'])->middleware('permission:LEER_EXAMENES');
        //ruta para archivar prueba de glucosa
        Route::put('/archive/{id}', [TestsController::class, 'archiveTest'])->middleware('permission:ARCHIVAR_EXAMENES');
        //ruta para restaurar prueba de glucosa
        Route::put('/restore/{id}', [TestsController::class, 'restoreTest'])->middleware('permission:RESTAURAR_EXAMENES');
        //ruta para eliminar prueba de glucosa
        Route::delete('/delete/{id}', [TestsController::class, 'deleteTest'])->middleware('permission:ELIMINAR_EXAMENES');
    });
});
