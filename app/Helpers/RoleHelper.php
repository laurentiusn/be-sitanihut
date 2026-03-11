<?php

namespace App\Helpers;

use App\Models\Role;

class RoleHelper
{ 
    /**
     * Mengambil ID role berdasarkan slug
     * 
     * @param string $slug string role
     * @return string UUID Role
     */
    public static function id(string $slug): string
    {
        return Role::where('slug', $slug)->value('id');
    }
}