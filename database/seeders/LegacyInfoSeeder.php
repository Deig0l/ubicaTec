<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Restaura la información recuperada de la BD legacy (MySQL de XAMPP):
 * descripciones e imágenes de los edificios y los contactos de departamentos.
 * Idempotente y por nombre: solo actualiza locaciones que ya existen, por lo
 * que puede correrse las veces que sea (también después de ubicatec:sync-locations).
 */
class LegacyInfoSeeder extends Seeder
{
    /** name → columnas a actualizar. */
    private const INFO = [
        'Coffee Shop' => [
            'description' => 'Puesto de café frente al gimnasio, donde puedes comprar café, matcha, paninis y otros snacks.',
            'phone' => '+52 (656) 3308840',
            // El registro original tenía el pin en el centro del campus; este es el kiosko real.
            'lat' => 31.7204372,
            'lng' => -106.421465,
        ],
        'Ciencias Básicas' => [
            'phone' => '(656) 688-2500 Ext. 2361',
            'email' => 'jefatura_cbas@cdjuarez.tecnm.mx',
            'website' => 'https://cbitcj.wixsite.com/basicas',
            'facebook' => 'https://www.facebook.com/cbasicasitcj',
        ],
        'Económico Administrativo' => [
            'phone' => '(656) 688-2500 Ext. 2381',
            'email' => 'jefatura_cead@cdjuarez.tecnm.mx',
            'website' => 'https://itcjdptoecon.wixsite.com/itcj4',
        ],
        'Eléctrica y Electrónica' => [
            'phone' => '(656) 688-2500 Ext. 2401',
            'email' => 'jefatura_eem@cdjuarez.tecnm.mx',
            'facebook' => 'https://www.facebook.com/ITCJ.Electrica.Electronica/',
        ],
        'Industrial y Logística' => [
            'phone' => '(656) 688-2500 Ext. 2421',
            'email' => 'jefatura_ii@cdjuarez.tecnm.mx',
            'facebook' => 'https://www.facebook.com/share/18bE1vm9g6/?mibextid=wwXIfr',
        ],
        'Metal Mecánica' => [
            'phone' => '(656) 688-2500 Ext. 2441',
            'email' => 'jefatura_mm@cdjuarez.tecnm.mx',
            'facebook' => 'https://www.facebook.com/share/1EBY1oystK/?mibextid=wwXIfr',
        ],
        'Sistemas Computacionales' => [
            'phone' => '(656) 688-2500 Ext. 2461',
            'email' => 'jefatura_syc@cdjuarez.tecnm.mx',
            'facebook' => 'https://www.facebook.com/share/1GRNt9J3rr/?mibextid=wwXIfr',
        ],
        'Alberca' => [
            'phone' => '+52 (656) 7054568',
        ],
        'Centro de Información (Biblioteca)' => [
            'phone' => '(656) 688-2500 Ext. 2181',
        ],
        'Gimnasio' => [
            'phone' => '(656) 688-2500 Ext. 2161',
        ],
        'Edificio Rivera Lara' => [
            'description' => 'Edificio principal del instituto. En él se encuentran la Sala de Alumnos, la Liebre Shop, el Consultorio Médico y la Sala de Descanso, además de aulas y oficinas en sus tres pisos.',
            'image' => 'images/locations/rivera_lara.jpg',
        ],
        'Laboratorio de manufactura' => [
            'description' => 'Laboratorio donde los alumnos realizan sus prácticas de manufactura. Cuenta con áreas de automatización, metrología, carpintería y máquinas-herramienta.',
            'image' => 'images/locations/manufactura.jpg',
        ],
        'Oficinas de mantenimiento' => [
            'description' => 'Oficinas donde se solicitan reparaciones y mantenimiento del equipo e instalaciones del instituto. Incluye talleres de carpintería y mantenimiento, y el área de jardinería.',
            'image' => 'images/locations/mantenimiento.jpg',
        ],
        'División de estudios' => [
            'description' => 'Edificio donde se encuentran los coordinadores de cada carrera y la coordinación de la división, además de oficinas de vinculación, investigación y proyectos docentes.',
            'image' => 'images/locations/div_estudios.jpg',
        ],
        'Centro de cómputo/Lab de contabilidad' => [
            'description' => 'Centro de cómputo donde alumnos y maestros pueden recuperar su usuario y contraseña institucionales y resolver asuntos de cómputo. A un lado se encuentra el laboratorio especializado para los alumnos de Contaduría.',
            'image' => 'images/locations/centro_comp.jpg',
        ],
        'Edificio Guillot' => [
            'description' => 'Edificio donde los alumnos acuden a asesorías, tutorías y trámites de titulación y residencias. Alberga el Laboratorio de Alta Tecnología (LAT), la incubadora de empresas y varias salas de titulación.',
            'image' => 'images/locations/gillot.jpg',
        ],
        'Aulas 400/Audiovisual' => [
            'description' => 'Edificio con las aulas de la serie 400 y el auditorio Audiovisual, donde se imparten clases y se proyectan eventos académicos.',
            'image' => 'images/locations/400s.jpg',
        ],
        'Edificio V - Centro de idiomas' => [
            'description' => 'Centro de idiomas del instituto, donde los alumnos toman cursos de idiomas extranjeros.',
            'image' => 'images/locations/centro_idiomas.jpg',
        ],
        'Edificio W - Posgrado' => [
            'description' => 'Edificio de posgrado, donde se imparten las clases de maestría y, en ocasiones, clases regulares de licenciatura.',
            'image' => 'images/locations/posgrado.jpg',
        ],
        "Edificio P - 800's" => [
            'description' => 'Edificio con las aulas de la serie 800, usadas principalmente por las carreras de Ingeniería Industrial y Logística.',
            'image' => 'images/locations/800s.jpg',
        ],
        'Auditorio Multifuncional' => [
            'description' => 'Auditorio donde se realizan conferencias, ceremonias y eventos especiales del instituto. Incluye el auditorio principal, uno secundario, la sala de ajedrez y la jefatura de Extraescolares.',
            'image' => 'images/locations/multi.jpg',
        ],
        'Edificio Y - Laboratorio de Mecatronica' => [
            'description' => 'Laboratorio con equipo especializado para prácticas de Mecatrónica: robótica, inteligencia artificial, IoT, impresión 3D y manufactura avanzada.',
            'image' => 'images/locations/meca.jpg',
        ],
        'Nodo' => [
            'description' => 'Espacio de innovación donde los estudiantes experimentan con herramientas y tecnología de punta.',
            'image' => 'images/locations/nodo.jpg',
        ],

        // Descripciones nuevas: estos espacios no tenían información en la BD legacy.
        'Edificio Eléctrica' => [
            'description' => 'Edificio de laboratorios de Eléctrica y Electrónica. Cuenta con el Laboratorio de Energías Alternativas, laboratorio de simulación y áreas de máquinas y control.',
        ],
        'Edificio Administración' => [
            'description' => 'Edificio administrativo del instituto. Aquí se encuentran Servicios Escolares, Recursos Humanos, Recursos Financieros y Caja, así como la Dirección y las subdirecciones Académica, Administrativa y de Planeación y Vinculación.',
        ],
        'Edificio T - Ingeniería Industrial' => [
            'description' => 'Edificio T, con los salones de la serie 900 usados principalmente por Ingeniería Industrial, además de cubículos y secretaría.',
        ],
        'Edificio Q - Editorial' => [
            'description' => 'Edificio Q, sede de la editorial del instituto y de oficinas administrativas como Compras y Activo Fijo.',
        ],
        'Cancha de Futbol' => [
            'description' => 'Cancha de futbol soccer del instituto, usada para clases de educación física, entrenamientos y torneos.',
        ],
        'Cancha de Baseball' => [
            'description' => 'Campo de béisbol del instituto, sede de entrenamientos y juegos de los equipos representativos.',
        ],
        'Canchas de basquetball' => [
            'description' => 'Canchas al aire libre de básquetbol, usadas para clases de educación física, torneos y juego libre.',
        ],
        'Cancha de Futbol Americano/Futbol Rapido' => [
            'description' => 'Cancha para futbol americano y futbol rápido, usada en clases, entrenamientos y torneos.',
        ],
        'Estacionamiento Centro de información' => [
            'description' => 'Estacionamiento junto al Centro de Información (Biblioteca).',
        ],
        'Estacionamiento Trasero' => [
            'description' => 'Estacionamiento en la parte trasera del campus.',
        ],
        'Estacionamiento principal' => [
            'description' => 'Estacionamiento principal del instituto, junto al acceso principal.',
        ],
        'Centro de Información' => [
            'description' => 'Edificio del Centro de Información (Biblioteca), con acervo bibliográfico, salas de estudio, cubículos y equipo de cómputo.',
        ],
        'Edificio Metal Mecánica' => [
            'description' => 'Edificio de talleres y laboratorios de Metal Mecánica: área de máquinas, instrumentación, materiales e ingeniería térmica.',
        ],
        'Edificio Sistemas' => [
            'description' => 'Edificio de Sistemas Computacionales, con los salones de la serie 700, oficinas de maestros y el departamento de Sistemas.',
        ],
    ];

    public function run(): void
    {
        $updated = 0;

        foreach (self::INFO as $name => $attributes) {
            $updated += Location::where('name', $name)->update($attributes);
        }

        $this->command?->info("LegacyInfoSeeder: {$updated} locaciones actualizadas.");
    }
}
