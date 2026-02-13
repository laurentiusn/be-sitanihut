<?php

namespace App\Http\Requests\Api\User;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // $me = $this->user();
        // $roleId = $this->input('id_role');
        // $roleToCreate = Role::find($roleId);

        // if(!$roleToCreate) {
        //     return false; // Role does not exist
        // }

        // if ($me->hasRole('sekretariat')) {
        //     return in_array($roleToCreate->slug, ['penyuluh', 'petani']);
        // }

        // if ($me->hasRole('penyuluh')) {
        //     return $roleToCreate->slug === 'petani';
        // }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'                 => 'required|email|max:100|unique:users,email',
            'password'              => ['required', 'string', Password::min(8)],
            'id_role'               => 'required|string|exists:role,id',
            'id_kph'                => 'nullable|string|exists:kph,id',
            'id_kth'                => 'nullable|string|exists:kth,id',

            'nama_user'             => 'required|string|max:30',
            'nomor_induk'           => 'required|string|max:20|unique:user_details,nomor_induk',
            'jenis_kelamin'         => 'required|in:pria,wanita',
            'profile_picture'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'alamat'                => 'nullable|string',
            'nomor_wa'              => 'nullable|string|max:20',
            'pendidikan_terakhir'   => 'nullable|string|max:10',
            'pekerjaan_sampingan'   => 'nullable|string|max:20',
            'luas_lahan'            => 'nullable|numeric',
            'jabatan'               => 'nullable|string|max:100',
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
