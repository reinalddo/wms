<?php use \koolreport\core\Utility; ?>
<div id="<?php echo $this->name; ?>" style="<?php echo Utility::get($this->css,"panel"); ?>" class="drilldown <?php echo Utility::get($this->cssClass,"panel","bg-transparent");?>">
    <div class="drilldown-body <?php echo Utility::get($this->cssClass,"body");?>" style="<?php echo Utility::get($this->css,"body"); ?>">

        <div class="row">
            <div class="col-sm-8">
                <?php
                if($this->showLevelTitle)
                {
                ?>
                    <ol style="padding:0px 15px;" class="breadcrumb <?php echo Utility::get($this->cssClass,"levelTitle");?>"  style="<?php echo Utility::get($this->css,"levelTitle"); ?>"></ol>
                <?php
                }
                ?>
            </div>
            <div class="col-sm-4 text-right" style="padding-right:30px;">
                <?php
                if($this->btnBack)
                {
                ?>
                <button style="<?php echo Utility::get($this->css,"btnBack"); ?>" type="button" onclick="<?php echo $this->name ?>.back()" class="btnBack <?php echo Utility::get($this->btnBack,"class",Utility::get($this->cssClass,"btnBack","btn btn-sm btn-primary")) ?>"><?php echo Utility::get($this->btnBack,"text","Back") ?></button>
                <?php    
                }
                ?>
            </div>
        </div>


        <?php
        for($i=0;$i<count($this->levels);$i++)
        {
        ?>
        <div class="drilldown-level drilldown-level-<?php echo $i; ?>"<?php echo ($levelIndex==$i)?"":" style='display:none'"; ?>>
            <?php
            if($levelIndex==$i)
            {
                $this->renderCurrentLevel();
            }
            ?>
        </div>
        <?php    
        }
        ?>
    </div>
</div>


<script type="text/javascript">
KoolReport.widget.init(<?php echo json_encode($this->getResources()); ?>,function(){
    <?php echo $this->name; ?> = new KoolReport.drilldown.DrillDown("<?php echo $this->name; ?>",<?php echo json_encode($options); ?>);
    <?php
    foreach($this->clientEvents as $event=>$function)
    {
    ?>
        <?php echo $this->name; ?>.on("<?php echo $event ?>",<?php echo $function; ?>);
    <?php
    }
    ?>
    <?php $this->clientSideReady();?>
});
</script>