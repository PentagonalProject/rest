<?php
/**
 * MIT License
 *
 * Copyright (c) 2017, Pentagonal
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author pentagonal <org@pentagonal.org>
 */

/**
 * Translations for id_ID (common use id as backward)
 */
return [
    // Containers/Global/NotAllowedHandler:60
    'Invalid Hook for Not Allowed Handler. Not Allowed Handler must be callable.' => 'Hook salah pada Not Allowed Handler. Not Allowed Handler harus callable',

    // Containers/Global/NotFoundHandler:60
    'Invalid Hook for Not Found Handler. Not Found Handler must be callable.' => 'Hook salah pada Not Found Handler. Not Found Handler harus callable',

    // Containers/Global/PhpErrorHandler:61
    'Invalid Hook for Php Error Handler. Php Error Handler must be callable.' => 'Hook salah pada Php Error Handler. Php Error Handler harus callable',

    // Middlewares/RestMiddleware:(78,109)
    'Target API endpoint has invalid' => 'API tujuan akhir salah.',
    'Method not allowed on current target API' => 'Metode tidak diijinkan untuk tujuan API saat ini',

    // Models/Handler/Role:(125, 152, 241, 246, 269, 274)
    'Role could not be empty' => 'Role tidak boleh kosong',
    'Status could not be empty' => 'Status tidak boleh kosong',
    'Default Role could not be empty' => 'Role Default tidak boleh kosong',
    'Role not exists' => 'Role tidak tersedia',
    'Default Status could not be empty' => 'Status Default tidak boleh kosong',
    'Status not exists' => 'Status tidak tersedia',

    // Models/Handler/UserAuthenticator:68
    'Not enough access' => 'Tidak cukup akses',

    // Models/Validator/UserValidator:(100, 121, 142)
    '%s is already used' => '%s telah digunakan',
    'Invalid username' => 'Username salah',
    'Invalid email address' => 'Alamat email salah',
];
