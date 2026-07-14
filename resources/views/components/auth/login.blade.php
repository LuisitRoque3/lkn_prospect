<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

new class extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|string',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
};
?>

<div class="min-h-screen bg-[#fdfaf6] flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
    <div class="sm:mx-auto w-full max-w-md">
        <div class="text-center space-y-2">
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[#a3583d] bg-[#a3583d]/10 px-3 py-1 rounded-full">
                Locknode CRM
            </span>
            <h2 class="mt-6 text-center text-3xl font-black uppercase tracking-tight text-[#3d2b1f]">
                Iniciar Sesión
            </h2>
            <p class="text-xs text-[#3d2b1f]/60 font-medium">Ingresa a tu panel de prospección inteligente.</p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto w-full max-w-md">
        <div class="bg-white py-8 px-4 border border-[#3d2b1f]/10 shadow-lg rounded-3xl sm:px-10">
            <form wire:submit="login" class="space-y-6">
                <div>
                    <label for="email" class="block text-xs font-black uppercase tracking-wider text-[#3d2b1f]/70">
                        Correo Electrónico
                    </label>
                    <div class="mt-1 relative">
                        <input id="email" 
                               wire:model="email" 
                               type="email" 
                               required 
                               autocomplete="email" 
                               placeholder="tu@correo.com"
                               class="appearance-none block w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] placeholder-[#3d2b1f]/40 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-black uppercase tracking-wider text-[#3d2b1f]/70">
                        Contraseña
                    </label>
                    <div class="mt-1 relative">
                        <input id="password" 
                               wire:model="password" 
                               type="password" 
                               required 
                               placeholder="••••••••"
                               class="appearance-none block w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] placeholder-[#3d2b1f]/40 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" 
                               wire:model="remember" 
                               type="checkbox" 
                               class="h-4 w-4 text-[#a3583d] focus:ring-[#a3583d]/20 border-[#3d2b1f]/10 rounded-lg">
                        <label for="remember" class="ml-2 block text-xs text-[#3d2b1f]/70 font-semibold cursor-pointer">
                            Recordarme
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-2xl shadow-md text-xs font-black uppercase tracking-wider text-white bg-[#a3583d] hover:bg-[#8f4730] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#a3583d] transition-all transform hover:-translate-y-0.5">
                        Ingresar al Panel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
