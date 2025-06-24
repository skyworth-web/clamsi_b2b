<div class="d-flex align-items-center">
    <div class="col-md-6 col-xl-6 page-info-title">
        <h3>{{ $title }}</h3>
        @if ($subtitle)
            <p class="sub_title">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="col-md-6 col-xl-6 d-flex justify-content-end">
        <nav aria-label="breadcrumb" class="float-end">
            <ol class="breadcrumb">
                <i class='bx bx-home-smile'></i>
                <li class="breadcrumb-item">
                    <a href="{{ route('delivery_boy.home') }}">{{ labels('admin_labels.home', 'Home') }}</a>
                </li>
                @foreach ($breadcrumbs as $crumb)
                    <li class="breadcrumb-item {{ $loop->last ? 'active' : 'second_breadcrumb_item' }}"
                        @if ($loop->last) aria-current="page" @endif>
                        @if (!$loop->last && isset($crumb['url']))
                            <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                        @else
                            {{ $crumb['label'] }}
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
</div>
