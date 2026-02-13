<x-mail::message>
    <div
        style="
                background-color: rgba(255, 255, 255, 0.9);
                border-radius: 0.5rem;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
                padding: 2rem;
                max-width: 40rem;
                width: 100%;
            ">
        <h1
            style="
                    font-size: 1.875rem;
                    font-weight: 600;
                    text-align: center;
                    margin-bottom: 1.5rem;
                ">
            Forgot Password
        </h1>
        <p
            style="
                    font-size: 1.125rem;
                    text-align: center;
                    margin-bottom: 1.5rem;
                ">
            Kamu menerima email ini karena kami menerima permintaan reset
            password untuk akun kamu.
        </p>
        <div
            style="
                    background-color: #f9fafb;
                    padding: 1rem;
                    border-radius: 0.5rem;
                    margin-bottom: 1.5rem;
                    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
                ">
            <p
                style="
                        font-size: 1rem;
                        color: #4b5563;
                        margin-bottom: 0.5rem;
                    ">
                * Jika kamu tidak merasa melakukan permintaan ini, abaikan
                email ini.
            </p>
            <p style="font-size: 1rem; color: #4b5563">
                * Email ini akan expired dalam waktu 15 menit.
            </p>
        </div>
        <x-mail::button :url="$url">
            Reset Password
        </x-mail::button>
    </div>
</x-mail::message>
