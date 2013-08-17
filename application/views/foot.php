</div><!-- end of main-container -->
<?=$this->javascript('js/combined')?>
<script src="/js/seajs/sea.js" id="seajsnode"></script>
<script>
    var templateList = ["people/list","people/edit"];
    var ENV = "develope";

    seajs.config({
      base: "/js/mod",
      plugins: ["text"],
      alias: {
        'select2':'/assets/js/select2'
      }
    });
    // 加载入口模块
    seajs.use("app");
</script>
</body>
</html>