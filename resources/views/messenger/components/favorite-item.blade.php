
    <div class="wsus__favourite_user">
        <div class="top">favourites</div>
        <div class="row favourite_user_slider">
            @foreach($favorites as $favorite)
                <div class="col-xl-3 messenger-list-item" data-id="{{ $favorite->user?->id }}">
                    <div class="wsus__favourite_item">
                        <div class="img">
                            <img
                                src="{{ asset($favorite->user?->avatar) }}"
                                alt="User"
                                class="img-fluid"
                            />
                            <span class="inactive"></span>
                        </div>
                        <p>{{ $favorite->user?->name }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

