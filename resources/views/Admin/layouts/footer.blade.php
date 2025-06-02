
 <!-- ======= Footer ======= -->



   <!-- Fixed footer -->
   <footer class="footer">
    <div class="container">
    <div class="copyright">
      &copy; Copyright <strong><span>My Backyard</span></strong>. All Rights Reserved
    </div>
    </div>
  </footer>
  <!-- jQuery -->

<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>

@yield('script')
<script>
    var div = document.querySelector(".toolTip");

    div.style.display = "flex";
    setTimeout(function() {
      div.style.display = "none";
    }, 3000);
  </script>
