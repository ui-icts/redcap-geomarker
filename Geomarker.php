<?php
namespace UIOWA\Geomarker;

class Geomarker extends \ExternalModules\AbstractExternalModule
{
    public function __construct()
    {
        parent::__construct();
    }

    public function display() {
        $json = $this->getRedcapMarkerData(
            $this->getProjectSetting('hover-field'),
            $this->getProjectSetting('lat-field'),
            $this->getProjectSetting('lng-field')
        );

        ?>
        <script type="text/javascript" src="<?= $this->getUrl('Geomarker.js') ?>"></script>

        <!-- Google Maps -->
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&key=<?= $this->getProjectSetting("google-maps-api-key") ?>"></script>

        <!-- Marker Manager -->
        <script type="text/javascript" src="<?= $this->getUrl('js/markerManager.js') ?>"></script>

        <script>
            Object.assign(UIOWA_Geomarker, {
                mapType: '<?= $this->getProjectSetting('map-type') ?>',
                data: '<?= htmlspecialchars($json, ENT_QUOTES) ?>'
            })
        </script>

        <div id="mapCanvas" style="width: 90%; height: 90%; position: relative"></div>
        <h5 id="mapStatus" style="padding: 10px"></h5>

        <?php
    }

    public function getRedcapMarkerData($hoverField, $latField, $lngField) {
        $recordIdField = \REDCap::getRecordIdField();
        $userRights = \REDCap::getUserRights()[USERID];
        $dagId = $userRights['group_id'];
        $allGroups = \REDCap::getGroupNames();
        $userDagName = $allGroups[$dagId];
        $fields = [$recordIdField, $hoverField, $latField, $lngField];
        $group = SUPER_USER == "1" ? NULL : $dagId;

        $getDataParams = [
            'return_format' => 'json',
            'fields' => $fields,
            'exportDataAccessGroups' => true,
            'groups' => $group
            
        ];

        $redcapData = json_decode(\REDCap::getData($getDataParams),true);
        $markerData = array();

        // obtain the name of the data collection instrument form
        $locationInstruments = json_decode(\REDCap::getDataDictionary('json', false, $fields), true);
        $pageName = json_decode(\REDCap::getDataDictionary('json', false, $hoverField), true)[0]['form_name'];

        $hasInstrumentAccess = false;
        foreach($locationInstruments AS $pages) {
            
            if(SUPER_USER == "1" || $userRights['forms'][$pages['form_name']] == "1" || $userRights['forms'][$pages['form_name']] == "2" ) {
                $hasInstrumentAccess = true;
                
            } else {
                $hasInstrumentAccess = false;
                break;
            }

        }

        if($hasInstrumentAccess) {
            foreach ( $redcapData as $fieldData ) {
                $newHash = array();
    
                foreach ( $fieldData as $key => $value ) {
    
                    if ( $key == $hoverField ) {
                        $newKey = "title";
                    }
                    elseif ( $key == $latField ) {
                        $newKey = "lat";
                    }
                    elseif ( $key == $lngField ) {
                        $newKey = "lng";
                    }
                    else {
                        $newKey = $key;
                    }

                    $newHash[$newKey] = $value;
                    
                }
    
                // build the URL to the record
                $url = sprintf( "%sDataEntry/index.php?pid=%d&page=%s&id=%s",
                    APP_PATH_WEBROOT, htmlentities($_REQUEST['pid'], ENT_QUOTES), $pageName, $fieldData[$recordIdField] );
    
                $newHash['url'] = $url;
    
                array_push($markerData, $newHash);
    
            }
        }

        return json_encode($markerData);
    }
}