<nav class="main-header navbar navbar-expand navbar-orange navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!--Profile and Logout Button-->
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="{{Helper::getUserProfilePicture()}}" class="user-image img-circle elevation-2" alt="User Image" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                <span class="d-none d-md-inline">{{auth::user()->name}}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-primary">
                    <img src="{{Helper::getUserProfilePicture()}}" class="img-circle elevation-2" alt="User Image" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                    <p>{{auth::user()->name}}</p>
                </li>
                <!-- Menu Body -->
                @if($menuBody = false)
                <li class="user-body">
                    <div class="row">
                        <div class="col-4 text-center">
                            <a href="#">Followers</a>
                        </div>
                        <div class="col-4 text-center">
                            <a href="#">Sales</a>
                        </div>
                        <div class="col-4 text-center">
                            <a href="#">Friends</a>
                        </div>
                    </div>
                </li>
                @endif
                <!-- Menu Footer-->
                <li class="user-footer">
                    <a href="{{route('admin.home.profile')}}" class="btn btn-default btn-flat">{{__('admin.profile')}}</a>
                    <a class="logout-link btn btn-default btn-flat float-right" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">{{__('admin.logout')}}</a>
                </li>
            </ul>
        </li>
        <!--/ Profile and Logout Button-->
    </ul>
    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</nav>