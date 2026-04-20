@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-6xl font-bold text-red-600">500</h1>
            <p class="mt-4 text-3xl font-bold text-gray-900">Server Error</p>
            <p class="mt-2 text-lg text-gray-600">
                {{ $message ?? 'An unexpected error occurred. Please try again later.' }}
            </p>
        </div>

        <div class="mt-6">
            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                Return to Dashboard
            </a>
        </div>

        <p class="mt-4 text-sm text-gray-500">
            If the problem persists, please contact the system administrator.
        </p>
    </div>
</div>
@endsection
