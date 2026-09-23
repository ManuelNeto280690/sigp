<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIGP') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

        <!-- Scripts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
       <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->

        <!-- Configuração removida - agora está apenas no tailwind.config.js -->
        
        <link href="{{asset('assets/style.css')}}" rel="stylesheet" />
       
        <script src"{{asset('js/app.js')}}"></script>
        
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0086e1 0%, #01043d 100%);
        }
        
        .login-bg {
            background-image: url('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAGQAZADASIAAhEBAxEB/8QAGgABAAMBAQEAAAAAAAAAAAAAAAMEBQIGAf/EAD0QAAIBAwMCBAQFAgUEAQUAAAECAwAEEQUSITFBBhMiUWEycYGRoRSxI0LB0eHwFWKS8QcXM1IXJCRyc//EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwD6qooooKJFxw1zj7V7K6vZGvLH+rOLf+m2h9Oz8Y9s/Kqp0eKKVOXJHLPn3z1q7t7D6CjPrdjOoOIUG1fLzLv9sdf61paHPdxaHcJa3MkcTybhIhwV21YGRz3NM0/SK9bGNhH5VBQ0jXrfUowwO2XHI7Ej3re2mggiip6qxWOt4Z3YtmWi9kTHfNeg6u+TI2JJKFx8pBHO2m9/NuDtzRKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKK3vC9zKLa8tN7MoO5evsay6KKKKKKKKdNVtVj2T/1onoZlR/L6K+Jf+/8AnxvDsE0dWJPEKJmOGFcHsO5rdq+qyRGNGzJGvbmgKGJZGVkKyKcEEYIIr7WJQ7LcQM3qXnHetcUUUUUUUUV9qBtoMjqD0O0xrv4z1Jq38H//1wq3VFFFFFFaQoo6I01u3v2+2qOlwgggE/YVpSSJZLLIwCLtP5PWt45Kq+UGI4GP1qhp9qZXEz/AN2sUrP0H1r3P9HWJGwDrnPtWjIggikd3VEHqdjgfWj8PrZHrlLmZJrNZJcySMW9OxBn3PJH2FYOiaFNPM0N08yqvqG3gn61qeFrmH/xhKLpVMe5H3xFmv8AmOp/0B9K0Y/B5v8AV57h9QEKzXLMv9LKqoPyx0AApegajaS6rNphWOC6WItGO6gkhj2yT9cdK86TkZ5qjQTkYrL0DTpbKSSZ7py5Q75pHKqg9wvXPPWhvUbSRe60gM+/+rGFz9P0z6aKmOKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKK8SSSFmZLtOW3H+iP2rNSb3vDBP6lkGvyfGn/3v3jXo7G7Sej9e2Bffs/X/AFVrXNw8cqGHKc+ypNl8lTrzx2rR03Vbc3cVgZXEzqxBjUu3Az3GM4/1rLFGRzyKlEoOAhH8P61t+C9QiFu/l2AmdMkuAFWqK2E+v/T/AF8W7L4pDGJjKmcDap+I8+ygnj5CqnjfV9WnudFi0qWc20cKySRQMVKOCTkbTwQuT86wBf3A66dqS++5/wCw4aSFJBHJhFdG+HHJb64PNMvbhPJW9lihYJtdYj5eD/MgAflRWLRRRRRRRRRRRRRRRRRRRRRRUJjzq+bKvNNFOJHvfLDuBgKgC5iw1enr8taP8f/AB9+fLO0m+uGaO2nSTd5TllyePgz7cAdawY5LbTP6VzOzEjDJHHJLn2y27H3NbWi+IJdLlMFxa3rWjeV5TyoBsyO4K4JA6j3rT1z/wDb2pXGm/6bLqLyvGSCVSPaGNZPiKO9qzBp1vMgjnTyzLGGVNzYKN8QxgjHUYPFYKvWK1t0ksC0TlkOwNjpqB3qSaKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKK3/CcGq6jZ3dzHJOuprOqOI0DDzNmVHPJOf3re/wDB2leHra6Pjm11WP8Aprg3Gbe4Gfic4JdeCflWG8fgO+vIdN8P3epNNqClrPzHEMklyyj0iRiOCRjP1+WOo6RpiW8R1nV5Ly1J3yRMk1vbW++L+tkP+ycDGRjO3pWTY/8Ah/xJe27W8+sGGUnY6+SXQMfcHGM4P8/b8U/+DGqWRjXQtWjmjaTazyL5O0N12luRwMjOM85rPOq2J8P6No1vbOl2lveFrqWRC8Tf1Af6e3PG7v8AIUHTtJupLMQW15BBbrE0hgljGNp5bnhvc/1OOBnPvUdl/wCDev6bdST2eo27RiKKRGlj3q25Ryudue/T2q6fAepaTrf+n6ldOBJJbiWCOBCwzkeVN17n0njt9zWjrN1/4O0HT3/0+51ie6vJNi3kscw8mREXbhCMNyQexJI6cFuJTwN4QlsWCRa3fSQ3M8VtKq3O2OOZh6XjIG7J5HQ9B3rnxd4T0y009tY8KalHd6LGz7TFK/8AVTG4Nuzn545zjjNdad4k0FPCem6lqt9M/wCs1HSDN5RlljO2WAu3PHb3z0GT2lv4n8A+FdKuIrGyvtV/1m1jmt5DqMZ8i6lJOwYxt69M8mto6b4u/wC5baYaVIZNO8vw9YMsjJ6jJJMrZJyTgCk7/Aun/wBNe654d1mXTs3+p6k+oQ3QQMVeEwxbBtJQqyjbzjGMDpUd3q/g7xDpt5a6No8Vo15OzyLf2s0bJcxqAGMnQkL8OSwxjkYrl7fw7q3iWKyGsX0ZhilZ9+lwSz+W65G0cYz0LAY9s1m+F/Csug6td3EV7Lar5Xo80sGEhIDKuCceYzN3zxXXhTQpdX8LareDTUfT9V1CHyNcaRZlNqRB5YMj+YCOCCVxz3NRf8J6T/41eH9P/wCvbqPzrWP7xYj/ALm5wPYGjcpUOaKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKK8xfXc9o2qWfJvktrlpJG9PQ5+IcsW6Lgdh0PWvOQNfSpcQ3f6rT/ADLj/TmEMzh9+05Qna21cEfCd1b/AIP0Kw8P/wDjOuwWMFpJrfgmyljjSMKomt/NjjYgebgFhk+2aDV0rw/pXgfSDG2qyyT3M1n5k1nexyPLIxO2Vdp9I9K5U/CfTjntZf8AhC/D8Gs3Xh2K28RNDrV3Hr0M92J3k82ZRH5buBuJKOB03cEilOyTTftlKqXlrfzGCNmFp5x8sRHOdgI6D2zXLx6hojKZ7K4t/MsL2c2k5Ro7cSe3A+Z+dA29HuTqui+J9SjXLw6lqtxC3HCJOVBrXsdC1DQv9V8MXeoCfT/K/XabMWdGRSTujJ5V2IPJOOh6VR8J6lrcvhe+0/UYLy509lhK/q4fKnlT1cFl+fXqT+avNJqV5ofi/wANWWo3E9/9+MV5YrL3VmNaD+q3h/TbG9ms5W3l+NJ6y8TqD2r58L2Gm6bPfXVgGW6uxA8dxJJ5k0jxBiWZuu7djnp/ap8/8QP+3h1LFrrP6bT97RfqF5PnGo3EJjE8nfb6zt55wepp6xrmkeMdQGhX+j6nNcKJJBJ5cqy3G8LuVQDyc8A/x1qpNaaFeRzQJb6kIZ7+F9Qit9u2e9dgVcyfEu4k5GSe9WP/AAnrOm+FvD1nfWNjcyah4r8E6tpl7JHgf8PN5skCyEOEJdCRggkNmtfQ/AfjL+rqBsfEWoav5UZltbFLfT4Q8I9TEkoCOCT/AFQcEjitvVtK8Uajp6X+qf6FZT2bNJbyx+YTAq8EhSP2P+lNjwX4oOjfpf01l/rX/wAtfX36dD6v7e+cVf8ADenS+GtK8Q63PLHJetrOowWjJIF2zQ7kUnH36fyKD3lFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFYevf9x+GL/8A7kV/g/6T3+H4/T6g+7Oe2exrMlhvNJto7eG9nWCXcscJCKjN7Pk5WtT/APDFX/7CeMdR/wCG/wD22/W6cfq+8n9J6fh5/wCN+1aUfhTWPD+tWuh/rUdG0eM2Fhp8YgaOyxGhQHtgY5HYe3vQWdCc6z4R8QaIyxh9N8OTFJQu4vCy7W6DkEfcCuT/AMJ6/wCGItWup9KXUJb8QJBq+kWskYaMsHCsf6YO7IbGfrWR4FufDvhEeJfD+sRalNGdAv7u71Gzhk3TzFkUBIQeAcn6DqcV4j/wlqOmaTrumadptmqSXHgPU49Q8lBsiNmZnAPqJGB/2cZpNp+m/wDbQfN/7en8R/Fux/Fx/wB/m9k/Qs7Uv/7fQeJfFGq6Nd/8N+H/ANFH9BYfp7bH9L4hjHtknFewn1HUNOtJ5pJGSGFFCqeDgDAHH8Vy+r6u0X9S6nMWNuyMeR0wOMfxRZ+KfEOt+HbN3tNHjWOdLWe/gKGC6IOehBwrDp6TzwOKC7/3L/6fz/8AJW//APJ/+L7H6c8+1eLsv/Cv/T7UtQt9B1XXte1e0vJLOxurrM0KW0rYijkkQKjMoAzitp/EXjjxGBBJ/p7XJOG2QiJ8knG8+nHIB4xWd/3OvLr/AK7Dv/1j9H8JgbYfu5w7en8fNB6/9f8A0rD/AG/Oq/8Az/8AIf6cde1Y/wD2/A/7j/8ALp/N8fr7/aupNR8baLcXs1/YaVILIGOGLToT58xjx6Wbsf7AUpfaZ4i8SxRtfXctg8qIRdOQQWYZKDHbI5PQdKC5pf8A1B/7d/8AzP8Av8Gfs/TH/wBmm//9k=');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
        }

        .login-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 134, 225, 0.8) 0%, rgba(1, 4, 61, 0.9) 100%);
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' fill='%23ffffff' fill-opacity='0.1'/%3E%3C/svg%3E") repeat-x;
            animation: wave 10s linear infinite;
        }

        @keyframes wave {
            0% { background-position-x: 0; }
            100% { background-position-x: 1200px; }
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 134, 225, 0.15);
        }

        .btn-hover {
            transition: all 0.3s ease;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 134, 225, 0.3);
        }

        .ship-icon {
            filter: drop-shadow(0 4px 8px rgba(0, 134, 225, 0.3));
        }

         .ripple {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            background-color: rgba(255, 255, 255, 0.3);
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
    </head>
    <body class="login-bg min-h-screen flex items-center justify-center relative" x-data="{ 
    showPassword: false, 
    rememberMe: false,
    loading: false,
    formData: {
        email: '',
        password: ''
    }
}">

<!-- Background Overlay -->
    <div class="login-overlay"></div>
    
    <!-- Animated Wave -->
    <div class="wave"></div>

     <!-- Login Container -->
    <div class="relative z-10 w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl mx-auto px-4 sm:px-6">
            
        
            <!-- Logo Section -->
        <div class="text-center mb-6 sm:mb-8 animate-float">
            <div class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 bg-white rounded-full shadow-2xl mb-3 sm:mb-4 ship-icon">
                <i class="fas fa-anchor text-primary text-xl sm:text-2xl md:text-3xl"></i>
            </div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-2 break-words">SIGP SOYO</h1>
            <p class="text-white/80 text-sm sm:text-base md:text-lg break-words">Sistema Integrado de Gestão Portuária</p>
        </div>

            <!-- Login Card -->
        <div class="login-card rounded-2xl sm:rounded-3xl shadow-2xl p-4 sm:p-6 md:p-8 w-full"> 

                {{ $slot }}



             <!-- System Info -->
     

    </div>



    </body>
</html>
