</div><!-- end of main-container -->
<?=$this->javascript('js/combined') ?>
<script src="/js/seajs/sea-debug.js" id="seajsnode"></script>
<script>
    var templateList = ["people/list","people/edit"];
    var ENV = "develope";
    var ENV = "product";

    seajs.config({
      base: "/js/mod",
      plugins: ["text","nocache"],
      alias: {
        'jquery':'/js/jquery-1.9.1',
        "bootstrap":"/js/bootstrap",
        "bootbox":"/js/bootbox",
        "jquery-ui":'/js/jquery-ui-1.10.3.custom',
        'select2':'/js/select2',
        'fullcalendar':'/js/fullcalendar'
      }
    });
    // 加载入口模块
    seajs.use("app");
    seajs.use("template-engine");
</script>
</body>
</html>