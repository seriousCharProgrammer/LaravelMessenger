
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi"
    />
    <meta
      name="description"
      content="Fastchat is a chat application that allows you to chat with your friends and family" />
    <meta name="id" content="" />
    <meta name="auth_id" content="{{auth()->user()->id}}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="url" content="{{ public_path() }}" />
    <title>Fastchat</title>
    <link rel="icon" type="image/png" href="{{asset('assets/images/5.png')}}"/>

    <link rel="stylesheet" href="{{asset('assets/css/all.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/slick.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/css/venobox.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/emojionearea.min.css')}}" />

    <link rel="stylesheet" href="{{asset('assets/css/spacing.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/css/responsive.css')}}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

   <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css">


    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
      integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />

     @vite(['resources/css/app.css', 'resources/js/app.js','resources/js/messenger.js'])
  </head>

  <body>

    @yield('content')



    <script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>

    <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>

    <script src="{{asset('assets/js/Font-Awesome.js')}}"></script>
    <script src="{{asset('assets/js/slick.min.js')}}"></script>
    <script src="{{asset('assets/js/venobox.min.js')}}"></script>
    <script src="{{asset('assets/js/emojionearea.min.js')}}"></script>
    <script src="{{asset('https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js')}}"></script>
    <script src="{{asset('https://unpkg.com/nprogress@0.2.0/nprogress.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://unpkg.com/peerjs@1.5.4/dist/peerjs.min.js"></script>
    <script src="https://www.WebRTC-Experiment.com/RecordRTC.js"></script>



    <script src="{{asset('assets/js/main.js')}}"></script>
    <script>

        const notyf = new Notyf({
    duration: 3000,
    position: {
        x: 'center',
        y: 'top'
    },
    dismissible: true, // Enables click-to-close functionality
    ripple: true, // Adds a ripple effect when clicking
    types: [
        {
            type: 'success',
            background: '#06B6D4',
            text: '',
            color: 'white',
            className: 'notyf__toast--dismissible', // Add class for dismissible styling
            ripple: true,
            dismissible: true
        },
        {
            type: 'info',
            background: '#06B6D4',
            text: '',
            color: 'white',
            className: 'notyf__toast--dismissible',
            ripple: true,
            dismissible: true
        },
        {
            type: 'warning',
            background: '#06B6D4',
            text: '',
            color: 'white',
            className: 'notyf__toast--dismissible',
            ripple: true,
            dismissible: true
        }
    ]
});
    </script>
    @stack('scripts')

  </body>
</html>
