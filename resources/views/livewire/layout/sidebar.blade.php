@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
@endphp



<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-[9999] flex h-screen w-[290px] flex-col overflow-hidden border-r border-gray-200 bg-white px-5 transition-transform duration-300 ease-in-out dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0">
    <div :class="sidebarToggle ? 'justify-center' : 'justify-between'" class="flex items-center gap-2 pt-8 pb-7">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" class="flex items-center gap-3">
            <span class="logo" :class="sidebarToggle ? 'lg:hidden' : ''">
                <img class="h-8 dark:hidden" src="{{ asset('assets/tailadmin/images/logo/logo.svg') }}" alt="Logo">
                <img class="hidden h-8 dark:block" src="{{ asset('assets/tailadmin/images/logo/logo-dark.svg') }}"
                    alt="Logo">
            </span>
            <img class="logo-icon h-8" :class="sidebarToggle ? 'hidden lg:block' : 'hidden'"
                src="{{ asset('assets/tailadmin/images/logo/logo-icon.svg') }}" alt="Logo icon">
        </a>
    </div>

    <div class="flex flex-1 flex-col overflow-y-auto pb-6 no-scrollbar">
        <nav>
            <div>
                <h3 class="mb-4 text-xs uppercase leading-5 text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">Menu</span>
                    <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                        class="mx-auto fill-current menu-group-icon" width="24" height="24" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                            fill="currentColor" />
                    </svg>
                </h3>

                <ul class="flex flex-col gap-2">
                    @foreach ($sidebarMenus as $menu)
                        @if ($menu->children->isNotEmpty())
                            <li x-data="{ open: {{ $menu->active ? 'true' : 'false' }} }">
                                <button type="button"
                                    class="menu-item group w-full text-left {{ $menu->active ? 'menu-item-active' : 'menu-item-inactive' }}"
                                    @click="open = !open">
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200
                                            text-gray-500 group-hover:border-brand-200 group-hover:text-brand-500
                                            dark:border-gray-800 dark:text-gray-400 dark:group-hover:text-brand-400">
                                        @if (!empty($menu->icon))
                                            @if (Str::contains($menu->icon, '<svg'))
                                                {!! $menu->icon !!}
                                            @else
                                                <i class="{{ $menu->icon }}"></i>
                                            @endif
                                        @else
                                            <span class="text-sm font-semibold uppercase">
                                                {{ Str::substr($menu->name, 0, 1) }}
                                            </span>
                                        @endif
                                    </span>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        {{ $menu->name }}
                                    </span>
                                    <svg class="menu-item-arrow {{ $menu->active ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive' }}"
                                        :class="open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive'"
                                        width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7 8L10 11L13 8" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <ul x-show="open" x-transition class="menu-dropdown flex-col gap-1 pl-12"
                                    :class="sidebarToggle ? 'lg:hidden flex' : 'flex'">
                                    @foreach ($menu->children as $child)
                                        <li>
                                            <a href="{{ $child->route_url ?? '#' }}"
                                                class="menu-dropdown-item {{ $child->active ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                                {{ $child->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li>
                                <a href="{{ $menu->route_url ?? '#' }}"
                                    class="menu-item group {{ $menu->active ? 'menu-item-active' : '' }}">
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-500 group-hover:border-brand-200 group-hover:text-brand-500 dark:border-gray-800 dark:text-gray-400 dark:group-hover:text-brand-400">
                                        @if (Str::contains($menu->icon, '<svg'))
                                            {!! $menu->icon !!}
                                        @else
                                            <i class="{{ $menu->icon }}"></i>
                                        @endif
                                    </span>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        {{ $menu->name }}
                                    </span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <div class="mt-8">
                <h3 class="mb-4 text-xs uppercase leading-5 text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">Account</span>
                </h3>
                <ul class="flex flex-col gap-2">
                    @if (Route::has('profile.edit'))
                        <li>
                            <a href="{{ route('profile.edit') }}"
                                class="menu-item group {{ Route::is('profile.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-500 group-hover:border-brand-200 group-hover:text-brand-500 dark:border-gray-800 dark:text-gray-400 dark:group-hover:text-brand-400">
                                    P
                                </span>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Profile</span>
                            </a>
                        </li>
                    @endif
                    @if (Route::has('logout'))
                        <li>
                            <form method="POST" action="{{ route('logout') }}"
                                class="menu-item group menu-item-inactive">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3">
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-500 group-hover:border-brand-200 group-hover:text-brand-500 dark:border-gray-800 dark:text-gray-400 dark:group-hover:text-brand-400">
                                        L
                                    </span>
                                    <span class="menu-item-text"
                                        :class="sidebarToggle ? 'lg:hidden' : ''">Logout</span>
                                </button>
                            </form>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>
    </div>
</aside>
