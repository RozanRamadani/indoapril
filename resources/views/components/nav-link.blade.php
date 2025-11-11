@props(['active' => false])

<a {{ $attributes->merge(['class' => 'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none ' . ($active ? 'border-white text-white' : 'border-transparent text-indigo-100 hover:text-white hover:border-indigo-300')]) }}>
    {{ $slot }}
</a>
