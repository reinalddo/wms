<?php

use \koolreport\core\Utility as Util;

$cMetas = Util::get($meta, "columns", []);

$tableCss = Util::get($this->cssClass, "table");
$trClass = Util::get($this->cssClass, "tr");
$tdClass = Util::get($this->cssClass, "td");
$thClass = Util::get($this->cssClass, "th");
$tfClass = Util::get($this->cssClass, "tf");

$tableAttrs = Util::get($this->attributes, "table");
$trAttrs = Util::get($this->attributes, "tr");
$tdAttrs = Util::get($this->attributes, "td");
$thAttrs = Util::get($this->attributes, "th");
$tfAttrs = Util::get($this->attributes, "tf");

$getMappedProperty = function ($mappedProperty, $default) {
    $args = func_get_args();
    $args = array_slice($args, 2);
    $property = is_callable($mappedProperty) ?
        call_user_func_array($mappedProperty, $args) : $mappedProperty;
    if (!isset($property)) $property = $default;
    return $property;
};

$draw = (int) Util::get($this->submitType, 'draw', 0);
$id = Util::get($this->submitType, 'id', null);
$ds = $this->dataStore;
$ajax = $this->serverSide && $id == $this->name;
if ($ajax) {
    echo "<dt-ajax id='dt_{$this->name}'>";
    $resData = [
        'draw' => $draw + 1,
        'recordsTotal' => Util::get($meta, 'totalRecords', 0),
        'recordsFiltered' => Util::get($meta, 'filterRecords', 0),
        'data' => $ds->data()
    ];
    echo json_encode($resData);
    echo "</dt-ajax>";
}
?>
<table id="<?php echo $this->name; ?>" <?php
                                        $attrs = $getMappedProperty($tableAttrs, [], $this->dataStore);
                                        foreach ($attrs as $k => $v) echo "$k='$v'";
                                        $cssClass = $getMappedProperty($tableCss, "table display", $this->dataStore);
                                        echo "class='$cssClass'";
                                        ?>>
    <thead>
        <?php if (!$this->complexHeaders) { ?>
            <tr>
                <?php
                foreach ($showColumnKeys as $ci => $cKey) {
                    $label = Util::get($cMetas, [$cKey, "label"], $cKey);
                    $cMeta = Util::get($cMetas, $cKey, []);
                ?>
                    <th <?php
                        $attrs = $getMappedProperty($thAttrs, [], $cKey, $cMeta);
                        foreach ($attrs as $k => $v) echo "$k='$v'";
                        $cssClass = $getMappedProperty($thClass, "", $cKey, $cMeta);
                        echo "class='$cssClass'"; ?>>
                        <?php echo $label; ?>
                    </th>
                <?php
                }
                ?>
            </tr>
        <?php } else {
            foreach ($headerRows as $aHeaderRow) {
                echo "<tr>";
                foreach ($aHeaderRow as $header) {
                    if (isset($header['text'])) {
                        $text = $header['text'];
                        $colspan = Util::get($header, 'colspan', 1);
                        $rowspan = Util::get($header, 'rowspan', 1);
                        $cssClass = $getMappedProperty($thClass, "", $text, []);
                        echo "<th class='$cssClass' colspan='$colspan' rowspan='$rowspan'>
                    $text</th>";
                    }
                }
                echo "</tr>";
            }
        } ?>
    </thead>
    <?php if (!$this->serverSide && !$this->fastRender) { ?>
        <tbody>
            <?php foreach ($this->dataRows as $i => $row) { ?>
                <tr <?php
                    $attrs = $getMappedProperty($trAttrs, [], $row, $cMetas);
                    foreach ($attrs as $k => $v) echo "$k='$v'";
                    $cssClass = $getMappedProperty($trClass, "", $row, $cMetas);
                    echo "class='$cssClass'";
                    ?>>
                    <?php
                    foreach ($showColumnKeys as $cKey) {
                        $cMeta = Util::get($cMetas, $cKey, []);
                    ?>
                        <td <?php
                            $attrs = $getMappedProperty($tdAttrs, [], $row, $cKey, $cMeta);
                            foreach ($attrs as $k => $v) echo "$k='$v'";
                            $cssClass = $getMappedProperty($tdClass, "", $row, $cKey, $cMeta);
                            echo "class='$cssClass'";
                            foreach (['data-order', 'data-search'] as $d)
                                if (isset($cMeta[$d]))
                                    echo "$d='" . Util::get($row, $cMeta[$d], '') . "'";
                            ?>>
                            <?php
                            echo $row[$cKey];
                            ?>
                        </td>
                    <?php
                    }
                    ?>
                </tr>
            <?php
            }
            ?>
        </tbody>
    <?php } ?>
    <?php
    if ($this->showFooter) {
    ?>
        <tfoot>
            <tr>
                <?php
                foreach ($showColumnKeys as $cKey) {
                    $cMeta = Util::get($cMetas, $cKey, []);
                ?>
                    <td <?php
                        $attrs = $getMappedProperty($tfAttrs, [], $cKey, $cMeta);
                        foreach ($attrs as $k => $v) echo "$k='$v'";
                        $cssClass = $getMappedProperty($tfClass, "", $cKey, $cMeta);
                        echo "class='$cssClass'";
                        ?>>
                        <?php
                        $footerMethod = strtolower(Util::get($cMetas, [$cKey, "footer"]));
                        $footerText = Util::get($cMetas, [$cKey, "footerText"]);
                        $footerValue = null;
                        switch ($footerMethod) {
                            case "sum":
                            case "min":
                            case "max":
                            case "avg":
                                $footerValue = $this->dataStore->$footerMethod($cKey);
                                break;
                            case "count":
                                $footerValue = $this->dataStore->countData();
                                break;
                        }
                        $footerValue = ($footerValue !== null) ? $this->formatValue($footerValue, $cMetas[$cKey], $cKey) : "";
                        if ($footerText) {
                            echo str_replace("@value", $footerValue, $footerText);
                        } else {
                            echo $footerValue;
                        }
                        ?>
                    </td>
                <?php
                }
                ?>
            </tr>
        </tfoot>
    <?php
    }
    ?>
</table>
<script type="text/javascript">
    KoolReport.widget.init(
        <?php echo json_encode($this->getResources()); ?>,
        function() {
            <?php $this->clientSideBeforeInit(); ?>

            var name = '<?php echo $uniqueId; ?>';
            var dtOptions = <?php echo ($this->options == array()) ? "" : Util::jsonEncode($this->options); ?>;
            var fastRender = <?php echo $this->fastRender ? 1 : 0; ?>;
            if (fastRender) {
                dtOptions.data = <?php echo json_encode($this->dataRows); ?>;
            }
            var dt = window[name] = $('#' + name).DataTable(dtOptions);

            var <?php echo $uniqueId; ?>_data = {
                id: '<?php echo $uniqueId; ?>',
                searchOnEnter: <?php echo $this->searchOnEnter ? 1 : 0; ?>,
                searchMode: <?php echo json_encode($this->searchMode); ?>,
                serverSide: <?php echo $this->serverSide ? 1 : 0; ?>,
                rowDetailData: dtOptions.rowDetailData,
                showColumnKeys: <?php echo json_encode($showColumnKeys); ?>,
                fastRender: fastRender,
                rowDetailIcon: <?php echo $this->rowDetailIcon ? 1 : 0; ?>,
                rowDetailSelector: '<?php echo $this->rowDetailSelector; ?>',
            };
            KR<?php echo $uniqueId; ?> = KoolReport.KRDataTables.create(
                <?php echo $uniqueId; ?>_data);

            <?php if ($this->clientEvents) {
                foreach ($this->clientEvents as $eventName => $function) { ?>
                    dt.on("<?php echo $eventName; ?>", <?php echo $function; ?>);
            <?php }
            } ?>

            <?php $this->clientSideReady(); ?>
        }
    );
</script>