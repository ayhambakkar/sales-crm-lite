<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Sign in to your account</h2>
    <p class="text-sm text-gray-500 mt-1">Enter your credentials to continue.</p>
</div>

<!-- Info message (e.g. after logout) -->
<?php if (! empty($info)): ?>
    <div class="mb-5 flex items-start gap-3 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <?= e($info) ?>
    </div>
<?php endif; ?>

<!-- Error message -->
<?php if (! empty($error)): ?>
    <div class="mb-5 flex items-start gap-3 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <?= e($error) ?>
    </div>
<?php endif; ?>

<!-- Login form -->
<form method="POST" action="/login" novalidate>
    <?= csrf_field() ?>

    <!-- Email -->
    <div class="mb-5">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
            Email address
        </label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= e($email ?? '') ?>"
            required
            autocomplete="email"
            autofocus
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900
                   placeholder-gray-400 shadow-sm
                   focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20
                   transition"
            placeholder="you@example.com"
        >
    </div>

    <!-- Password -->
    <div class="mb-6">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
            Password
        </label>
        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900
                   placeholder-gray-400 shadow-sm
                   focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20
                   transition"
            placeholder="••••••••"
        >
    </div>

    <!-- Submit -->
    <button
        type="submit"
        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white
               shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500
               focus:ring-offset-2 active:bg-blue-800 transition"
    >
        Sign In
    </button>
</form>
