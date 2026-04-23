  <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="/admin/assets/img/folu_logo.png"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="/admin/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
      custom: {
        families: [
          "Font Awesome 5 Solid",
          "Font Awesome 5 Regular",
          "Font Awesome 5 Brands",
          "simple-line-icons",
        ],
          urls: ["/admin/assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="/admin/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/admin/assets/css/plugins.min.css" />
    <link rel="stylesheet" href="/admin/assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="/admin/assets/css/demo.css" />
      <link rel="stylesheet" href="/admin/assets/css/sweetalert.css">
    <script src="/admin/assets/css/sweetalert.min.js"></script>

<style>
.tree {
  text-align: center;
  padding: 40px 10px;
}

.tree ul {
  padding-top: 20px;
  position: relative;
  display: flex;
  justify-content: center;
}

.tree li {
  list-style: none;
  position: relative;
  margin: 0 15px;
  padding-top: 30px;
  text-align: center;
}

.tree li::before, .tree li::after {
  content: '';
  position: absolute;
  top: 0;
  border-top: 2px dotted #ccc;
  width: 50%;
  height: 20px;
}

.tree li::before {
  left: -50%;
  border-right: 2px dotted #ccc;
}

.tree li::after {
  right: -50%;
  border-left: 2px dotted #ccc;
}

.tree li:only-child::before,
.tree li:only-child::after {
  content: none;
}

.tree .node {
  display: inline-block;
  text-align: center;
}

.tree .node img {
  width: 80px;
  height: 80px;
  border: 3px solid #001F3F;
  border-radius: 50%;
  object-fit: cover;
}

.tree .node .name {
  margin-top: 5px;
  font-size: 14px;
  font-weight: bold;
}

.tree .no-user img {
  opacity: 0.3;
  border-color: #ccc;
}

.comp{
  color:red;
}
</style>


<?php /**PATH C:\laragon\www\folu\resources\views/admin/partials/links.blade.php ENDPATH**/ ?>