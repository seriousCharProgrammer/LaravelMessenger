<div class="wsus__user_list">
    <div class="wsus__user_list_header">
      <h3 class="main-app-header">
        <span
          ><img
            src="{{asset('assets/images/5.png')}}"
            alt="Chat"
            class="img-fluid"
        /></span>
        FastChat Lightning-Fast<span class="fa-solid fa-bolt" style="color: #0d6efd;"></span>


      </h3>
      <span
        class="setting"
        data-bs-toggle="modal"
        data-bs-target="#exampleModal"
      >
        <i class="fas fa-user-cog"></i>
      </span>

      <div
        class="modal fade"
        id="exampleModal"
        tabindex="-1"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
      >
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-body">
              <form action="#" class="profile-form" enctype="multipart/form-data">
                @csrf
                <div class="file profile-file">
                  <img
                    src="{{asset(auth()->user()->avatar)}}"
                    alt="Upload"
                    class="img-fluid profile-image-preview"
                  />
                  <label for="select_file"
                    ><i class="fal fa-camera-alt"></i
                  ></label>
                  <input id="select_file" type="file" hidden  name="avatar"/>
                </div>
                <p>Edit information</p>
                <input type="text" placeholder="Name" value="{{auth()->user()->name}}"  name="name"/>
                <input type="email" placeholder="Email" value="{{auth()->user()->email}}" name="email"/>
                <input type="text" placeholder="UserName" value="{{auth()->user()->username}}" name="user_id"/>
                <p>Change password</p>
                <div class="row">
                  <div class="col-xl-6">
                    <input type="password" placeholder="Current Password" name="current_password"/>
                  </div>
                  <div class="col-xl-6">
                    <input type="password" placeholder="New Password" name="password" />
                  </div>
                  <div class="col-xl-12">
                    <input type="password" placeholder="Confirm Password"  name="password_confirmation"/>
                  </div>
                </div>
                <div class="modal-footer p-0 mt-4">
                    <button
                    type="button"
                    class="btn btn-secondary cancel"
                    data-bs-dismiss="modal"
                  >
                    Close
                  </button>
                  <button type="submit" class="btn btn-primary save">
                    Save changes
                  </button>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>
  @include('messenger.layouts.search-form')

    <div class="wsus__favourite_user">
      <div class="top">favourites</div>
      <div class="row favourite_user_slider">
        <div class="col-xl-3">
          <a href="#" class="wsus__favourite_item">
            <div class="img">
              <img
                src=""
                alt=""
                class="img-fluid"
              />
              <span class="inactive"></span>
            </div>
            <p>mr hasin</p>
          </a>
        </div>

      </div>
    </div>

    <div class="wsus__save_message">
      <div class="top">your space</div>
      <div class="wsus__save_message_center messenger-list-item" data-id={{auth()->user()->id}}>
        <div class="icon">
          <i class="far fa-bookmark"></i>
        </div>
        <div class="text">
          <h3>Saved Messages</h3>
          <p>Save messages secretly</p>
        </div>
        <span>you</span>
      </div>
    </div>

    <div class="wsus__user_list_area">
      <div class="top">All Messages</div>
      <div class="wsus__user_list_area_height messenger-contacts">


      </div>



      <!-- <div class="wsus__user_list_liading">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div> -->
    </div>
  </div>

  @push('scripts')
  <script>
      $(document).ready(function() {
          $('.profile-form').on('submit', function(e){
              e.preventDefault();
              let saveBtn = $('.profile-save');
              let formData = new FormData(this);
              $.ajax({
                  method: 'POST',
                  url: '{{ route("profile.update") }}',
                  data: formData,
                  processData: false,
                  contentType: false,
                  beforeSend: function() {
                      saveBtn.text('saving...');
                      saveBtn.prop("disabled", true);
                  },
                  success: function(data) {
                      window.location.reload();
                  },
                  error: function(xhr, status, error) {
                      console.log(xhr)
                      let errors = xhr.responseJSON.errors;

                      $.each(errors, function(index, value) {
                          notyf.error(value[0]);
                      })

                      saveBtn.text('Save changes');
                      saveBtn.prop("disabled", false);

                  }
              })

          })
      })
  </script>
  @endpush

