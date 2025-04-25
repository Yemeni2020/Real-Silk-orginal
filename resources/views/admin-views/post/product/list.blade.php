@extends('layouts.back-end.app')

@section('title', translate('post_List'))

@section('content')
    <div class="content container-fluid">

        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                @if($type == 'in_house')
                    {{ translate('in_House_post_List') }}
                @elseif($type == 'seller')
                    {{ translate('vendor_post_List') }}
                @endif
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $posts->total() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ url()->current() }}" method="GET">
                    <input type="hidden" value="{{ request('status') }}" name="status">
                    <div class="row gx-2">
                        <div class="col-12">
                            <h4 class="mb-3">{{ translate('filter_post') }}</h4>
                        </div>

                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label for="name" class="title-color">{{ translate('category') }}</label>
                                <select class="js-select2-custom form-control action-get-request-onchange" name="category_id"
                                        data-url-prefix="{{ url('/admin/products/get-categories?parent_id=') }}"
                                        data-element-id="sub-category-select"
                                        data-element-type="select">
                                    <option value="{{ old('category_id') }}" selected
                                            disabled>{{ translate('select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}"
                                                {{ request('category_id') == $category['id'] ? 'selected' : '' }}>
                                            {{ $category['defaultName'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="{{ route('admin.products.list',['type'=>request('type')]) }}"
                                   class="btn btn-secondary px-5">
                                    {{ translate('reset') }}
                                </a>
                                <button type="submit" class="btn btn--primary px-5 action-get-element-type">
                                    {{ translate('show_data') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4">
                        <div class="row align-items-center">
                            <div class="col-lg-4">

                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="input-group input-group-custom input-group-merge">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="searchValue"
                                               class="form-control"
                                               placeholder="{{ translate('search_by_post_Name') }}"
                                               aria-label="Search orders"
                                               value="{{ request('searchValue') }}">
                                        <input type="hidden" value="{{ request('status') }}" name="status">
                                        <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-8 mt-3 mt-lg-0 d-flex flex-wrap gap-3 justify-content-lg-end">

                                @if($type == 'in_house')
                                    <a href="{{ route('admin.post.add') }}" class="btn btn--primary">
                                        <i class="tio-add"></i>
                                        <span class="text">{{ translate('add_new_post') }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable"
                               class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('post Name') }}</th>
                                <th class="text-center">{{ translate('post Type') }}</th>
                                <th class="text-center">{{ translate('vendor') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($posts as $key=>$post)
                                <tr>
                                    <th scope="row">{{ $posts->firstItem()+$key}}</th>
                                    <td style="white-space: normal; word-wrap: break-word; max-width: 200px;">
                                        <a href="{{ route('admin.products.view',['addedBy'=>($post['added_by']=='seller'?'vendor' : 'in-house'),'id'=>$post['id']]) }}"
                                           class="media align-items-center gap-2">
                                            <img src="{{ getStorageImages(path: $post->thumbnail_full_url, type: 'backend-product') }}"
                                                 class="avatar border" alt="">
                                            <span class="media-body title-color hover-c1">
                                            {{ $post['name']  }}
                                        </span>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {{ translate(str_replace('_',' ',$post['product_type'])) }}
                                    </td>
                                    <td class="text-center">
                                    @if($post->admins)
                                    <span class="text-muted">
                                            {{ $post->admins->name }}
                                    </span>
                                    @else
                                        <span class="text-muted">{{translate('admin')}}</span>
                                    @endif

                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a class="btn btn-outline-info btn-sm square-btn"
                                               title="{{ translate('barcode') }}"
                                               href="{{ route('admin.products.barcode', [$post['id']]) }}">
                                                <i class="tio-barcode"></i>
                                            </a>
                                            <a class="btn btn-outline-info btn-sm square-btn" title="View"
                                               href="{{ route('admin.products.view',['addedBy'=>($post['added_by']=='seller'?'vendor' : 'in-house'),'id'=>$post['id']]) }}">
                                                <i class="tio-invisible"></i>
                                            </a>
                                            <a class="btn btn-outline--primary btn-sm square-btn"
                                               title="{{ translate('edit') }}"
                                               href="{{ route('admin.products.update',[$post['id']]) }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <span class="btn btn-outline-danger btn-sm square-btn delete-data"
                                               title="{{ translate('delete') }}"
                                               data-id="product-{{ $post['id']}}">
                                                <i class="tio-delete"></i>
                                            </span>
                                        </div>
                                        <form action="{{ route('admin.products.delete',[$post['id']]) }}"
                                              method="post" id="product-{{ $post['id']}}">
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            {{ $posts->links() }}
                        </div>
                    </div>

                    @if(count($posts)==0)
                        @include('layouts.back-end._empty-state',['text'=>'no_product_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
        </div>
    </div>
    <span id="message-select-word" data-text="{{ translate('select') }}"></span>
@endsection
