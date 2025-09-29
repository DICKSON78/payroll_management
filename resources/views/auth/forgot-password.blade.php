<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
                <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
                <p class="text-primary font-semibold mt-2">Enter your email to receive reset instructions</p>
            </div>

            <!-- Form Content -->
            <div class="p-6 pt-4">
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Email Address</label>
                        <div class="relative">
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                class="input-field w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition bg-accent-light text-gray-600 @error('email') border-red-500 @enderror"
                                placeholder="Email address">
                            <i class="fas fa-envelope input-icon"></i>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" class="w-full premium-gradient text-white font-medium py-3 px-4 rounded-xl transition duration-200 shadow-md hover:shadow-lg button-glow">
                            <i class="fas fa-paper-plane mr-2"></i> Send Reset Link
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

            // Auto-hide icons when user starts typing
            const emailInput = document.getElementById('email');
            
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    const icon = this.parentElement.querySelector('.input-icon');
                    if (this.value.length > 0) {
                        icon.style.opacity = '0';
                    } else {
                        icon.style.opacity = '1';
                    }
                });

                emailInput.addEventListener('focus', function() {
                    const icon = this.parentElement.querySelector('.input-icon');
                    icon.style.opacity = '0';
                });

                emailInput.addEventListener('blur', function() {
                    const icon = this.parentElement.querySelector('.input-icon');
                    if (this.value.length === 0) {
                        icon.style.opacity = '1';
                    }
                });
            }
        });
    </script>
</body>
</html>