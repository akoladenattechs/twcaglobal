<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - {{ $siteSettings['site_title'] ?? config('app.name') }}</title>
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    @php
        $pc = $siteSettings['primary_color'] ?? '#ce0f3d';
        $sc = $siteSettings['secondary_color'] ?? '#343a40';
        $pcLight = $pc . '18';
        $pcLight50 = $pc . '80';
    @endphp
    <style>
        :root {
            --primary-color: {{ $pc }};
            --secondary-color: {{ $sc }};
            --primary-color-light: {{ $pcLight }};
            --primary-color-light-50: {{ $pcLight50 }};
        }
    </style>
    <link rel="stylesheet" href="{{ asset('admin/assets/css/admin.css') }}?v={{ filemtime(public_path('admin/assets/css/admin.css')) }}">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ \App\Helpers\HtmlHelper::assetUrl($siteSettings['favicon'] ?? '') }}">
    @yield('styles')
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</head>
<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="container-fluid p-0">
        <div class="row no-gutters">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
                <div class="sidebar-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="{{ \App\Helpers\HtmlHelper::assetUrl($siteSettings['logo'] ?? '') }}" alt="Logo" class="img-fluid sidebar-logo">
                    </div>
                    <!-- Admin Navigation Menu -->
                    <ul class="nav flex-column">
                        @permission('view_dashboard')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                            </a>
                        </li>
                        @endpermission
                        @permission('view_sections','manage_sections','view_events','manage_events','view_quotes','manage_quotes')
                        <li class="nav-header">Homepage</li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.hero') ? 'active' : '' }}" href="{{ route('admin.hero') }}">
                                <i class="fas fa-play-circle mr-2"></i> Hero Section
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.ministry-columns') ? 'active' : '' }}" href="{{ route('admin.ministry-columns') }}">
                                <i class="fas fa-columns mr-2"></i> Ministry Core
                            </a>
                        </li>
                        @endpermission
                        @permission('view_events','manage_events')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.events') ? 'active' : '' }}" href="{{ route('admin.events') }}">
                                <i class="fas fa-calendar-alt mr-2"></i> Events
                            </a>
                        </li>
                        @endpermission
                        @permission('view_quotes','manage_quotes')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.quotes') ? 'active' : '' }}" href="{{ route('admin.quotes') }}">
                                <i class="fas fa-quote-right mr-2"></i> Quotes
                            </a>
                        </li>
                        @endpermission
                        @permission('view_pages','manage_pages','view_books','manage_books','view_sermons','manage_sermons','view_devotionals','manage_devotionals','view_songs','manage_songs','view_inbox','manage_inbox','view_newsletters','send_newsletters')
                        <li class="nav-header">Content Management</li>
                        @endpermission
                        @permission('view_pages','manage_pages')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.about') ? 'active' : '' }}" href="{{ route('admin.about') }}">
                                <i class="fas fa-info-circle mr-2"></i> About Us
                            </a>
                        </li>
                        @endpermission
                        @permission('view_books','manage_books')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.books') ? 'active' : '' }}" href="{{ route('admin.books') }}">
                                <i class="fas fa-book mr-2"></i> Books
                            </a>
                        </li>
                        @endpermission
                        @permission('view_sermons','manage_sermons')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sermons') ? 'active' : '' }}" href="{{ route('admin.sermons') }}">
                                <i class="fas fa-microphone-alt mr-2"></i> Teachings
                            </a>
                        </li>
                        @endpermission
                        @permission('view_devotionals','manage_devotionals')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.devotionals') ? 'active' : '' }}" href="{{ route('admin.devotionals') }}">
                                <i class="fas fa-pray mr-2"></i> Devotionals
                            </a>
                        </li>
                        @endpermission
                        @permission('view_songs','manage_songs')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.songs') ? 'active' : '' }}" href="{{ route('admin.songs') }}">
                                <i class="fas fa-music mr-2"></i> Songs
                            </a>
                        </li>
                        @endpermission
                        @permission('view_inbox','manage_inbox')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.inbox') ? 'active' : '' }}" href="{{ route('admin.inbox') }}">
                                <i class="fas fa-inbox mr-2"></i> Inbox
                            </a>
                        </li>
                        @endpermission
                        @permission('view_newsletters','send_newsletters')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.newsletters') ? 'active' : '' }}" href="{{ route('admin.newsletters') }}">
                                <i class="fas fa-mail-bulk mr-2"></i> Newsletter
                            </a>
                        </li>
                        @endpermission
                        @permission('view_members','manage_members','view_attendance','manage_attendance','view_offerings','manage_offerings','view_staff','manage_staff')
                        <li class="nav-header">Church Management</li>
                        @endpermission
                        @permission('view_members','manage_members')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.members') ? 'active' : '' }}" href="{{ route('admin.members') }}">
                                <i class="fas fa-users mr-2"></i> Members
                            </a>
                        </li>
                        @endpermission
                        @permission('view_attendance','manage_attendance')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.attendance') ? 'active' : '' }}" href="{{ route('admin.attendance') }}">
                                <i class="fas fa-calendar-check mr-2"></i> Attendance
                            </a>
                        </li>
                        @endpermission
                        @permission('view_offerings','manage_offerings')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.financials*') ? 'active' : '' }}" href="{{ route('admin.financials') }}">
                                <i class="fas fa-coins mr-2"></i> Financials
                            </a>
                        </li>
                        @endpermission
                        @permission('view_dashboard')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
                                <i class="fas fa-chart-bar mr-2"></i> Reports
                            </a>
                        </li>
                        @endpermission
                        @permission('view_staff','manage_staff')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.staff') ? 'active' : '' }}" href="{{ route('admin.staff') }}">
                                <i class="fas fa-user-tie mr-2"></i> Staffs
                            </a>
                        </li>
                        @endpermission
                        @permission('view_settings','manage_settings','view_users','manage_roles','view_menus','manage_menus')
                        <li class="nav-header">Settings</li>
                        @endpermission
                        @permission('view_dashboard')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}" href="{{ route('admin.profile') }}">
                                <i class="fas fa-user-circle mr-2"></i> Profile
                            </a>
                        </li>
                        @endpermission
                        @permission('view_users','manage_roles')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                                <i class="fas fa-users-cog mr-2"></i> Users
                            </a>
                        </li>
                        @endpermission
                        @permission('manage_roles')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.roles') ? 'active' : '' }}" href="{{ route('admin.roles') }}">
                                <i class="fas fa-user-shield mr-2"></i> Roles & Permissions
                            </a>
                        </li>
                        @endpermission
                        @permission('view_menus','manage_menus')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.menus') ? 'active' : '' }}" href="{{ route('admin.menus') }}">
                                <i class="fas fa-bars mr-2"></i> Menus
                            </a>
                        </li>
                        @endpermission
                        @permission('view_settings','manage_settings')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.site-settings') ? 'active' : '' }}" href="{{ route('admin.site-settings') }}">
                                <i class="fas fa-cog mr-2"></i> Site Settings
                            </a>
                        </li>
                        @endpermission
                        @permission('view_activity_logs')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}" href="{{ route('admin.audit-logs') }}">
                                <i class="fas fa-clipboard-list mr-2"></i> Audit Logs
                            </a>
                        </li>
                        @endpermission

                        <li class="nav-item">

                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- Main Content Area -->
            <main role="main" class="content-wrapper">
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            $('#sidebarToggle').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('body').toggleClass('sidebar-active');
            });
            $(document).on('click', function(e) {
                if ($(window).width() <= 767.98) {
                    if (!$(e.target).closest('#sidebar').length && 
                        !$(e.target).closest('#sidebarToggle').length && 
                        $('#sidebar').hasClass('active')) {
                        $('#sidebar').removeClass('active');
                        $('body').removeClass('sidebar-active');
                    }
                }
            });

            // ── Global Delete / Action Confirmation Modal ──
            $(document).on('click', '[data-delete-action]', function() {
                var action = $(this).data('delete-action');
                var payload = $(this).data('delete-payload');
                var message = $(this).data('delete-message') || 'Are you sure you want to delete this record? This action cannot be undone.';
                var title = $(this).data('title') || 'Confirm Deletion';
                var btnText = $(this).data('btn-text') || 'Delete';
                var btnClass = $(this).data('btn-class') || 'btn-danger';
                var iconClass = $(this).data('icon-class') || 'fas fa-trash';

                // Set form action
                $('#globalDeleteForm').attr('action', action);

                // Clear and rebuild hidden fields from payload
                var $container = $('#globalDeleteFields');
                $container.empty();
                if (payload && typeof payload === 'object') {
                    $.each(payload, function(name, value) {
                        $container.append('<input type="hidden" name="' + name + '" value="' + value + '">');
                    });
                }

                $('#globalDeleteTitle').html('<i class="' + iconClass + ' mr-2"></i> ' + title);
                $('#globalDeleteMessage').text(message);
                $('#globalDeleteSubmitBtn').removeClass().addClass('btn ' + btnClass).html('<i class="' + iconClass + ' mr-1"></i> ' + btnText);
                $('#globalDeleteModal').modal('show');
            });

            // ── Fix aria-hidden focus warnings on modal hide (global) ──
            $(document).on('hide.bs.modal', '.modal', function() {
                if (document.activeElement && $(document.activeElement).closest(this).length) {
                    document.activeElement.blur();
                }
            });
        });
    </script>
    @yield('scripts')

    <!-- Global Delete Confirmation Modal -->
    <div class="modal fade" id="globalDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="" id="globalDeleteForm">
                    @csrf
                    <div id="globalDeleteFields"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="globalDeleteTitle"><i class="fas fa-exclamation-triangle text-danger mr-2"></i> Confirm Deletion</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="globalDeleteMessage">Are you sure you want to delete this record? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="globalDeleteSubmitBtn"><i class="fas fa-trash mr-1"></i> Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
