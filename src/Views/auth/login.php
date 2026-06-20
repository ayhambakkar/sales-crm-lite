<div class="mb-6">
    <h2 class="text-xl font-semibold text-slate-950">Sign in to your account</h2>
    <p class="mt-1 text-sm text-slate-500">Enter your credentials to continue.</p>
</div>

<?php if (! empty($info)): ?>
    <div class="mb-5 flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-800">
        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <?= e($info) ?>
    </div>
<?php endif; ?>

<?php if (! empty($error)): ?>
    <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <?= e($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="/login" class="space-y-5" novalidate>
    <?= csrf_field() ?>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">
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
            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-950
                   placeholder-slate-400 shadow-sm
                   focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20
                   transition"
            placeholder="admin@example.com"
        >
    </div>

    <div>
        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">
            Password
        </label>
        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-950
                   placeholder-slate-400 shadow-sm
                   focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20
                   transition"
            placeholder="Password"
        >
    </div>

    <button
        type="submit"
        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white
               shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500
               focus:ring-offset-2 active:bg-blue-800 transition"
    >
        Sign In
    </button>
</form>
