<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'email' => 'sometimes|required|email|max:100|unique:users,email,' . $userId,
            'password' => ['nullable', 'string', Password::min(8)],
            'role_slug' => 'sometimes|required|string|exists:roles,slug',
            'id_kph' => 'nullable|string|exists:kph,id',
            'id_kth' => 'nullable|string|exists:kth,id',

            'nama_user' => 'sometimes|required|string|max:30',
            'nomor_induk' => 'sometimes|required|string|max:20|unique:user_details,nomor_induk,' . $userId . ',id_user',
            'jenis_kelamin' => 'sometimes|required|in:pria,wanita',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'alamat' => 'nullable|string',
            'nomor_wa' => 'nullable|string|max:20',
            'pendidikan_terakhir' => 'nullable|string|max:10',
            'pekerjaan_sampingan' => 'nullable|string|max:20',
            'luas_lahan' => 'nullable|numeric',
            'jabatan' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_induk.unique' => 'Nomor induk sudah digunakan.',
            'nomor_induk.required' => 'Nomor induk wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
        ];
    }


    public function validationData(): array
    {
        return $this->all();
    }
}
