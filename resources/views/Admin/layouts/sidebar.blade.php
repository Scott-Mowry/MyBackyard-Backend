<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : 'collapsed' }} "
        href="{{route('admin.dashboard')}}">
        <i class="bi bi-speedometer text-primary"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->


    <li class="nav-item">
      <a class="nav-link {{ request()->is('admin/categories*') ? 'active' : 'collapsed' }}"
        href="{{route('admin.categories')}}">
        <i class="bi bi-briefcase text-secondary"></i>
        <span>Categories</span>
      </a>
    </li>
    <!--<li class="nav-item">-->
    <!--  <a class="nav-link {{ request()->is('admin/stock*') ? 'active' : 'collapsed' }}" href="{{route('admin.stock_images')}}">-->
    <!--    <i class="bi bi-images text-secondary"></i>-->
    <!--    <span>Stock Images</span>-->
    <!--  </a>-->
    <!--</li>-->

    <li class="nav-item">
      <a class="nav-link {{ request()->is('admin/users*') ? 'active' : 'collapsed' }}" href="{{route('admin.users')}}">
        <i class="bi bi-person-circle text-secondary"></i>
        <span>Users</span>
      </a>
    </li>

    <!-- <li class="nav-item">
    <a class="nav-link {{ request()->is('admin/manage_words*') ? 'active' : 'collapsed' }}" href="{{route('admin.manage_words')}}">
      <i class="bi bi-lightbulb text-secondary"></i>
      <span>Manage Words</span>
    </a>
  </li> -->


    <li class="nav-item">
      <a class="nav-link {{ request()->is('admin/subscribedUsers*') ? 'active' : 'collapsed' }}"
        href="{{route('admin.subUsers')}}">

        <i class="bi bi-list-check text-secondary"></i>
        <span>Subscribed Users</span>
      </a>
    </li>

    @if (Auth::user()->role == 'Admin')
    <li class="nav-item">
      <a class="nav-link {{ request()->is('admin/places*') ? 'active' : 'collapsed' }}"
      href="{{route('admin.places')}}">
      <i class="bi bi-map text-secondary"></i>
      <span>Allowed Places</span>
      </a>
    </li>
  @endif

    @if (Auth::user()->role == 'Admin')
    <li class="nav-item">
      <a class="nav-link {{ request()->is('admin/promocodes*') ? 'active' : 'collapsed' }}"
      href="{{route('admin.promocodes')}}">

      <i class="bi bi-ticket-perforated text-secondary"></i>
      <span>Promo Codes</span>
      </a>
    </li>
  @endif

    @if (Auth::user()->role == 'Admin')
    <li class="nav-item">
      <a class="nav-link {{ request()->is('admin/cms*') ? 'active' : 'collapsed' }}" href="{{route('admin.cms')}}">

      <i class="bi bi-file-lock text-secondary"></i>
      <span>CMS</span>
      </a>
    </li>
  @endif
    <!-- <li class="nav-item">
    <a class="nav-link {{ request()->is('admin/upload-files*') ? 'active' : 'collapsed' }}" href="{{route('admin.upload-file')}}">
      <i class="bi bi-briefcase text-secondary"></i>
      <span>Upload Files</span>
    </a>
  </li> -->


    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="bi bi-box-arrow-right text-danger"></i>
        <span>Logout</span>
      </a>
    </li>


  </ul>

</aside><!-- End Sidebar-->