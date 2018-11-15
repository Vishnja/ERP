<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ $currentUser->photoUrl }}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ $currentUser->fullname }}</p>
            </div>
        </div>
        <!-- /.user-panel -->

        <!-- sidebar menu: style can be found in sidebar.less -->
        {!! $menu !!}
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside><!-- /.main-sidebar -->

