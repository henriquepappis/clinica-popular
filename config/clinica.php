<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Preço padrão da consulta
    |--------------------------------------------------------------------------
    |
    | Valor usado como fallback quando não há preço configurado para o
    | médico, especialidade ou duração da consulta.
    |
    */

    'default_appointment_price' => env('CLINIC_DEFAULT_APPOINTMENT_PRICE', 100.00),

];
