@php
    use App\Enums\GlobalConstant;
    use App\Utils\FileManagerLogic;
@endphp
@if (isset($chattingMessages))
    @foreach($chattingMessages as $key => $message)
        @if ($message->sent_by_seller || $message->sent_by_admin || $message->sent_by_delivery_man)
            <li class="incoming" data-bs-toggle="tooltip"
                @if($message->created_at->diffInDays() > 6)
                    title="{{ $message->created_at->format('M-d-Y H:i:s') }}"
                @else
                    title="{{ $message->created_at->format('l H:i:s') }}"
                @endif
            >
                <img loading="lazy" class="img" alt="{{ translate('user') }}"
                     src="{{ $userType == 'admin' ? getStorageImages(path: $web_config['fav_icon'], type: 'shop') : ( $userType == 'vendor' ? getStorageImages(path: $message?->shop?->image_full_url, type: 'shop') : getStorageImages(path: $message?->deliveryMan?->image_full_url, type: 'avatar')) }}">
                <div class="msg-area">
                    @if($message->message)
                        <div class="msg">
                            {{$message->message}}
                        </div>
                    @endif
                    @if (count($message->attachment_full_url) > 0)
                        <div class="d-flex flex-wrap g-2 gap-2 justify-content-start custom-image-popup-init" data-bs-title="{{ date('h:i:A | M d',strtotime($message->created_at)) }}">
                            @php($index = 0)
                            @foreach ($message->attachment_full_url as $attachment)
                                @php($extension = strrchr($attachment['key'],'.'))
                                @if(in_array($extension, GlobalConstant::DOCUMENT_EXTENSION))
                                    @php($icon = in_array($extension,['.pdf','.doc','docx','.txt']) ? 'word-icon': 'default-icon')
                                    @php($downloadPath = $attachment['path'])
                                    <div class="d-flex gap-2 mt-2">
                                        <a href="{{$downloadPath}}" target="_blank">
                                            <div class="uploaded-file-item gap-2"><img
                                                    src="{{dynamicAsset('public/assets/front-end/img/word-icon/'.$icon.'.png')}}"
                                                    class="file-icon" alt="">
                                                <div class="upload-file-item-content">
                                                    <div>
                                                        {{($attachment['key'])}}
                                                    </div>
                                                    <small>{{FileManagerLogic::getFileSize($downloadPath)}}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @else
                                    <div class="position-relative overflow-hidden rounded-16px {{$index > 3 ? 'd-none' : ''}}">
                                        <a class="inbox-image-element custom-image-popup rounded-16px" href="{{getStorageImages(path: $attachment, type:'product') }}">
                                            <img loading="lazy" src="{{ getStorageImages(path: $attachment, type:'product') }}"
                                                 class="rounded" alt="{{ translate('verification') }}">
                                            @if($index > 2)
                                                <div class="extra-images show-extra-images">
                                                    <span class="extra-image-count">
                                                        +{{ count($message->attachment_full_url) - $index }}
                                                    </span>
                                                </div>
                                            @endif
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </li>
        @else
            <li class="outgoing" id="outgoing_msg">
                <div class="msg-area" data-bs-toggle="tooltip"
                     @if($message->created_at->diffInDays() > 6)
                         title="{{ $message->created_at->format('M-d-Y H:i:s') }}"
                     @else
                         title="{{ $message->created_at->format('l H:i:s') }}"
                    @endif
                >
                    @if($message->message)
                        <div class="msg">
                            {{$message->message}}
                        </div>
                    @endif
                    @if (count($message->attachment_full_url) >0)
                        <div class="d-flex flex-wrap g-2 gap-2 justify-content-end custom-image-popup-init" data-bs-toggle="tooltip" data-bs-title="{{ date('h:i:A | M d',strtotime($message->created_at)) }}">
                            @foreach ($message->attachment_full_url  as $secondIndex => $attachment)
                                @php($extension = strrchr($attachment['key'],'.'))
                                @if(in_array($extension,GlobalConstant::DOCUMENT_EXTENSION))
                                    @php($icon = in_array($extension,['.pdf','.doc','docx','.txt']) ? 'word-icon': 'default-icon')
                                    @php($downloadPath =$attachment['path'])
                                    <div class="d-flex gap-2 mt-2">
                                        <a href="{{$downloadPath}}" target="_blank">
                                            <div class="uploaded-file-item gap-2"><img
                                                    src="{{dynamicAsset('public/assets/front-end/img/word-icon/'.$icon.'.png')}}"
                                                    class="file-icon" alt="">
                                                <div class="upload-file-item-content">
                                                    <div>
                                                        {{($attachment['key'])}}
                                                    </div>
                                                    <small>{{FileManagerLogic::getFileSize($downloadPath)}}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @else
                                    <div class="position-relative overflow-hidden rounded-16px {{$secondIndex > 3 ? 'd-none' : ''}}">
                                        <a class="inbox-image-element custom-image-popup rounded-16px" href="{{ getStorageImages(path: $attachment, type:'product') }}">
                                            <img loading="lazy" src="{{ getStorageImages(path: $attachment, type:'product') }}"
                                                 class="rounded" alt="{{ translate('verification') }}">
                                            @if($secondIndex > 2)
                                            <div class="extra-images show-extra-images">
                                                <span class="extra-image-count">
                                                    +{{ count($message->attachment_full_url) - $secondIndex }}
                                                </span>
                                            </div>
                                            @endif
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </li>
        @endif
    @endForeach
    <div id="down"></div>
@endif
