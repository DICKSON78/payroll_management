<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            light: '#10a37f',
                            DEFAULT: '#1a7f64',
                            dark: '#156352',
                        },
                        secondary: {
                            DEFAULT: '#1F2937',
                            dark: '#111827',
                        },
                        accent: {
                            DEFAULT: '#F3F4F6',
                            light: '#F9FAFB',
                        }
                    },
                    boxShadow: {
                        'card': '0 8px 24px rgba(0, 0, 0, 0.15)',
                        'card-hover': '0 12px 32px rgba(0, 0, 0, 0.2)',
                    },
                    borderRadius: {
                        'xl': '1rem',
                        '2xl': '1.5rem',
                    }
                }
            }
        }
    </script>
    <style>
        .premium-gradient {
            background: linear-gradient(135deg, #10a37f 0%, #1a7f64 100%);
        }
        .loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #10a37f;
            transform: scaleX(0);
            transform-origin: left;
            animation: loading 5s ease-in-out forwards;
            z-index: 1000;
        }
        @keyframes loading {
            0% { transform: scaleX(0); }
            50% { transform: scaleX(0.8); }
            100% { transform: scaleX(1); opacity: 0; }
        }
        .button-glow {
            position: relative;
            overflow: hidden;
        }
        .button-glow::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        .button-glow:hover::before {
            left: 100%;
        }
        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 0.75rem;
            color: #1F2937;
            font-size: 1rem;
            display: flex;
            align-items: center;
            height: 100%;
            z-index: 10;
            transition: opacity 0.2s ease;
        }
        .input-field {
            padding-left: 2.5rem;
            line-height: 1.5rem;
        }
        /* Hide icon when input has value or is focused */
        .input-field:focus + .input-icon,
        .input-field:not(:placeholder-shown) + .input-icon {
            opacity: 0;
            pointer-events: none;
        }
        /* Ensure placeholder is visible */
        .input-field::placeholder {
            color: #9CA3AF;
            opacity: 1;
        }
    </style>
</head>
<body class="font-poppins bg-accent text-gray-600 min-h-screen flex items-center justify-center p-4">
    <!-- Loading Bar -->
    <div id="loading-bar" class="loading-bar"></div>

    <!-- Main Content -->
    <div class="w-full max-w-md">
        <div class="bg-accent-light rounded-2xl shadow-card overflow-hidden">
            <!-- Header -->
            <div class="bg-accent-light p-6 border-b border-primary/10 text-center">
                <div class="flex justify-center items-center mb-4">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-16">
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Create New Password</h1>
                <p class="text-primary font-semibold mt-2">Enter your new password below</p>
            </div>

            <!-- Form Content -->
            <div class="p-6 pt-4">
                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Email Address</label>
                        <div class="relative">
                            <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                                class="input-field w-full px-4 py-3 border border-primary/20 rounded-lg bg-gray-100 cursor-not-allowed text-gray-600"
                                placeholder="Email address">
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-600 mb-1">New Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                class="input-field w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition bg-accent-light text-gray-600 @error('password') border-red-500 @enderror"
                                placeholder="New password">
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-primary password-toggle">
                                <i class="far fa-eye-slash"></i>
                            </button>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Confirm Password Input -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-600 mb-1">Confirm Password</label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                class="input-field w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition bg-accent-light text-gray-600"
                                placeholder="Confirm new password">
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-primary password-toggle">
                                <i class="far fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" class="w-full premium-gradient text-white font-medium py-3 px-4 rounded-xl transition duration-200 shadow-md hover:shadow-lg button-glow">
                            <i class="fas fa-save mr-2"></i> Reset Password
                        </button>
                    </div>

                    <!-- Back to Login -->
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-primary hover:text-primary-dark text-sm font-medium">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Trigger loading bar on page load
        document.addEventListener('DOMContentLoaded', function() {
            const loadingBar = document.getElementById('loading-bar');
            loadingBar.style.display = 'block';
            setTimeout(() => {
                loadingBar.style.display = 'none';
            }, 2000);

            // Password toggle functionality
            const passwordToggles = document.querySelectorAll('.password-toggle');

            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                });
            });

            // Auto-hide icons when user starts typing
            const inputs = document.querySelectorAll('input[type="password"], input[type="email"]:not([readonly])');

            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    const icon = this.parentElement.querySelector('.input-icon');
                    if (icon && this.value.length > 0) {
                        icon.style.opacity = '0';
                    } else if (icon) {
                        icon.style.opacity = '1';
                    }
                });

                input.addEventListener('focus', function() {
                    const icon = this.parentElement.querySelector('.input-icon');
                    if (icon) {
                        icon.style.opacity = '0';
                    }
                });

                input.addEventListener('blur', function() {
                    const icon = this.parentElement.querySelector('.input-icon');
                    if (icon && this.value.length === 0) {
                        icon.style.opacity = '1';
                    }
                });
            });
        });
    </script>
</body>
</html>
