<!--**********************************
            Sidebar start
        ***********************************-->
<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="material-icons">dialpad</i>
                    <span class="nav-text">شماره مجازی معمولی</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{route('admin.Virtual-Countries')}}">کشور ها</a></li>
                    <li><a href="{{route('admin.Virtual-Services')}}">سرویس ها</a></li>
                    <li><a href="{{route('admin.Virtual-Products')}}">محصولات شماره مجازی</a></li>

                </ul>
            </li>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="material-icons">video_settings</i>

                    <span class="nav-text"> خدمات </span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{route('admin.category list',['product'])}}">دسته بندی محصولات</a></li>
                    <li><a href="{{route('admin.product list')}}">خدمات </a></li>

                </ul>
            </li>
            <li>
                <a class="ai-icon" href="{{route('admin.blog list')}}" aria-expanded="false">
                    <i class="material-icons">newspaper</i>
                    <span class="nav-text"> بلاگ ها </span>
                </a>
            </li>
            <li>
                <a class="ai-icon" href="{{route('admin.order list')}}" aria-expanded="false">
                    <i class="material-icons">card_giftcard</i>
                    <span class="nav-text"> سفارشات </span>
                </a>
            </li>
            <li>
                <a class="ai-icon" href="{{route('admin.tickets list')}}" aria-expanded="false">
                    <i class="material-icons">chat</i>
                    <span class="nav-text"> تیکت ها </span>
                </a>
            </li>
            <li>
                <a class="ai-icon" href="{{route('admin.users list')}}" aria-expanded="false">
                    <i class="material-icons">group</i>
                    <span class="nav-text"> کاربران </span>
                </a>
            </li>
        </ul>


    </div>
</div>
<!--**********************************
    Sidebar end
***********************************-->
