<?php $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://'; ?>
<script>var BASE_URL = "<?php echo $protocol.$_SERVER['HTTP_HOST'];?>";</script>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue-resource@1.5.1"></script>
<script src="/js/toolsvue.js"></script>
<script>
  var API_URL = "/api/reportesRutas/index.php";
</script>