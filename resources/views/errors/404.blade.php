@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-6xl font-bold text-yellow-600">404</h1>
            <p class="mt-4 text-3xl font-bold text-gray-900">Page Not Found</p>
            <p class="mt-2 text-lg text-gray-600">
                The page you are looking for could not be found.
            </p>
        </div>

        <div class="mt-6">
            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                Return to Dashboard
            </a>
        </div>

        <p class="mt-4 text-sm text-gray-500">
            If you believe this is an error, please contact support.
        </p>
    </div>
</div>
@endsection
