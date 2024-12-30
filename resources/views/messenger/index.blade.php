
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi"
    />
    <title>Chatting Application</title>
    <link rel="icon" type="image/png" href="images/favicon.png" />
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


    <script src="{{asset('assets/js/main.js')}}"></script>
    <script>
        var notyf = new Notyf({
            duration: 5000
        });
    </script>
    @stack('scripts')
  </body>
</html>
