<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

/**
 * class control all things related to plugin settings
 * @author grojanteam
 *
 */
class GJMAA_Controller_Settings extends GJMAA_Controller
{

    protected $content;

    protected $buttons = [
        'Add' => '&action=add'
    ];

    protected $parent = 'gjmaa_dashboard';
    
    protected $additionalInfo;

    public function getName()
    {
        $action = $this->getParam('action');

        switch ($action) {
            default:
                return 'Plugin settings';
            case 'edit':
                return 'Edit settings';
            case 'add':
                return 'Add settings';
        }
    }

    public function renderView()
    {
        $html = '<div class="wrap">' . $this->getTitle() . $this->getAdditionalInfo() . $this->getContent() . '</div>';

        echo $html;
    }

    public function getMenuName()
    {
        return __('Plugin settings', GJMAA_TEXT_DOMAIN);
    }

    /**
     * display table for settings
     */
    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getAdditionalInfo()
    {
        return $this->additionalInfo ? $this->additionalInfo : '';
    }

    public function setAdditionalInfo($additionalInfo = '')
    {
        $this->additionalInfo = $additionalInfo;
    }

    public function index()
    {
        $table = GJMAA::getTable('settings');
        $this->setContent($table->show());
    }

    public function add()
    {
        $this->buttons = [];
        $this->edit(0);
    }

    public function edit($id = null)
    {
        $this->buttons = [];
        $form = GJMAA::getForm('settings');
        $form->prepareForm();
        
        $allegroSite = GJMAA::getSource('allegro_site');
        
        $additionalInfo = '<h4 class="text-red">'.__('Please fill all required fields and save',GJMAA_TEXT_DOMAIN). '</h4>';

        if (is_null($id)) {
            $id = $this->getParam('setting_id');

            $model = GJMAA::getModel('settings');
            $model->load($id);

            $helper = GJMAA::getHelper('settings');

            if($model->getData('setting_site') == $allegroSite::ALLEGRO_PL_SITE){
                if (! $helper->isConnectedApi($model->getData())) {
                    if ($model->getData('setting_client_id') && $model->getData('setting_client_secret')) {
                        $this->buttons = [
                            'Connect' => sprintf('&action=connect&setting_id=%d', $id)
                        ];
                        
                        $additionalInfo = '<h4 class="text-red">'.__('Click on "Connect" to get Client Token.',GJMAA_TEXT_DOMAIN) . '</h4>';
                    } else {
                        $this->buttons = [
                            'Create App' => sprintf('&action=create_app&setting_id=%d', $id)
                        ];
                        
                        $additionalInfo = '<h4 class="text-red">'.__('Click on "Create App" to get Client ID, Client Secret.',GJMAA_TEXT_DOMAIN) . ' ' . __('Fill required fields and save.',GJMAA_TEXT_DOMAIN) . '</h4>';
                        $additionalInfo .= '<p>'.__('In your new application copy and paste Redirect URI', GJMAA_TEXT_DOMAIN) . ': ' . $this->getRedirectUri().'</p>';
                    }
                } else {
                    $this->buttons = [
                        'Disconnect' => sprintf('&action=disconnect&setting_id=%d', $id)
                    ];
                    
                    $additionalInfo = '<h4 class="text-green">'.__('API successfully connected.',GJMAA_TEXT_DOMAIN) .'</h4>';
    
                    if ($helper->isExpiredToken($model->getData())) {
                        $this->buttons['Refresh Token'] = sprintf('&action=refresh_token&setting_id=%d', $id);
                    }
                }
            } else {
                if($model->getData('setting_password') && $model->getData('setting_webapi_key')){
                    if(!$helper->checkWebAPIConnection($model->getData())){
                        $additionalInfo = '<h4 class="text-red">'.__('Please check your details and correct.',GJMAA_TEXT_DOMAIN).'</h4>';
                    } else {
                        $additionalInfo = '<h4 class="text-green">'.__('API successfully connected.',GJMAA_TEXT_DOMAIN) .'</h4>';
                    }
                }
            }

            $form->setValues($model->getData());
        }

        $form->generate();
        $this->setAdditionalInfo($additionalInfo);
        $this->setContent($form->toHtml());
    }

    public function create_app()
    {
        $setting_id = $this->getParam('setting_id');

        $settingModel = GJMAA::getModel('settings');
        $setting = $settingModel->load($setting_id);

        $createAppUrl = $setting->getData('setting_is_sandbox') ? 'https://apps.developer.allegro.pl.allegrosandbox.pl' : 'https://apps.developer.allegro.pl';

        $url = $this->getIndexUrl() . '&action=edit&setting_id=' . $setting_id;
        echo '<script type="text/javascript">
var newWin = window.open("' . $createAppUrl . '","_blank","width=400,height=400");
if(is_not_new_window_open(newWin)) 
{ 
    setInterval(function() {
        if(!is_not_new_window_open(newWin)) {
            location.href = "' . $url . '";
        }
    }, 1000);
} else {
    location.href = "' . $url . '";
}

function is_not_new_window_open(newWin) {
   return !newWin || newWin.closed || typeof newWin.closed=="undefined"; 
}

</script>';

        return;
    }

    public function save()
    {
        $params = $this->getParams();
        if (empty($params)) {
            $this->redirect($this->getIndexUrl(true));
            return;
        }
        
        $setting_id = $this->getParam('setting_id');
        
        if(!empty($params['setting_user_country']) && $params['setting_user_country'] != 'PL') {
            $params['setting_user_province'] = '';
        }

        try {
            $model = GJMAA::getModel('settings');
            $model->setData($params);
            $model->save();
            
            if(!$setting_id){
                $setting_id = $model->getData('setting_id');
            }

            $this->addSessionSuccess(__('Plugin setting saved successfully.',GJMAA_TEXT_DOMAIN));
        } catch (Exception $e) {
            $this->addSessionError($e->getMessage());
        }
        
        $this->redirect($this->getIndexUrl() . '&action=edit&setting_id=' . $setting_id);
        
    }

    public function delete()
    {
        $setting_id = $this->getParam('setting_id');
        if (! $setting_id) {
            $this->redirect($this->getIndexUrl(true));
            return;
        }

        try {
            $model = GJMAA::getModel('settings');
            $model->load($setting_id);
            $model->delete();

            $this->addSessionSuccess(__('Plugin setting removed successfully.',GJMAA_TEXT_DOMAIN));
        } catch (Exception $e) {
            $this->addSessionError($e->getMessage());
        }

        $this->redirect($this->getIndexUrl(true));
    }

    public function connect()
    {
        $setting_id = $this->getParam('setting_id');
        if (! $setting_id) {
            $this->redirect($this->getIndexUrl(true));
            return;
        }

        $model = GJMAA::getModel('settings');
        $model->load($setting_id);

        $code = $this->getParam('code');
        if (! $code) {
            try {
                $restLibConnect = GJMAA::getLib('rest_api_auth_code');
                $restLibConnect->setSandboxMode($model->getData('setting_is_sandbox'));
                $restLibConnect->setClientId($model->getData('setting_client_id'));
                $restLibConnect->setRedirectUri($this->getRedirectUri());
                $response = $restLibConnect->execute();

                echo '
					<script type="text/javascript">
						window.open("' . $response . '","_blank","width=400,height=400");
						window.onAuthorizedCode = function(code){
							location.href = location.href + "&code=" + code; 
						}
					 </script>';
            } catch (Exception $e) {
                $this->addSessionError($e->getMessage());
                $this->redirect($this->getIndexUrl() . '&action=edit&setting_id=' . $setting_id);
            }
            return;
        }

        try {
            $restLibConnect = GJMAA::getLib('rest_api_auth_token');
            $restLibConnect->setSandboxMode($model->getData('setting_is_sandbox'));
            $restLibConnect->setClientId($model->getData('setting_client_id'));
            $restLibConnect->setClientSecret($model->getData('setting_client_secret'));
            $restLibConnect->setCode($code);
            $restLibConnect->setRedirectUri($this->getRedirectUri());
            $response = $restLibConnect->execute();

            $model->setData('setting_client_token', $response['token']);
            $model->setData('setting_client_token_expires_at', date('Y-m-d H:i:s', time() + $response['expiresIn']));
            $model->setData('setting_client_refresh_token', $response['refreshToken']);
            $model->save();

            $this->addSessionSuccess(__('API successfully connected.',GJMAA_TEXT_DOMAIN));
        } catch (Exception $e) {
            $this->addSessionError($e->getMessage());
        }

        $this->redirect($this->getIndexUrl() . '&action=edit&setting_id=' . $setting_id);
    }

    public function disconnect()
    {
        $setting_id = $this->getParam('setting_id');
        if (! $setting_id) {
            $this->redirect($this->getIndexUrl(true));
            return;
        }

        try {
            $model = GJMAA::getModel('settings');
            $model->load($setting_id);

            $model->setData('setting_client_token', null);
            $model->setData('setting_client_refresh_token', null);
            $model->setData('setting_client_token_expires_at', null);
            $model->save();
            $this->addSessionSuccess(__('API successfully disconnected',GJMAA_TEXT_DOMAIN));
        } catch (Exception $e) {
            $this->addSessionError($e->getMessage());
        }

        $this->redirect($this->getIndexUrl() . '&action=edit&setting_id=' . $setting_id);
    }

    public function refresh_token()
    {
        $setting_id = $this->getParam('setting_id');
        if (! $setting_id) {
            $this->redirect($this->getIndexUrl(true));
            return;
        }

        try {
            $model = GJMAA::getModel('settings');
            $model->load($setting_id);

            try {
            	/** @var GJMAA_Helper_Settings $helper */
            	$helper = GJMAA::getHelper('settings');
            	$helper->refreshToken($model);
            } catch (Exception $exception) {
	            $model->setData('setting_client_token', null);
	            $model->setData('setting_client_refresh_token', null);
	            $model->setData('setting_client_token_expires_at', null);
	            $model->save();

	            $this->connect();
            }

            $this->addSessionSuccess(__('Token refreshed successfully.',GJMAA_TEXT_DOMAIN));
        } catch (Exception $e) {
            $this->addSessionError($e->getMessage());
        }

        $this->redirect($this->getIndexUrl() . '&action=edit&setting_id=' . $setting_id);
    }

    public function authorisedCode()
    {
        $code = $this->getParam('code');
        echo <<<HTML
<script type="text/javascript">
window.opener.onAuthorizedCode('{$code}');
close();
</script>
HTML;
        return;
    }

    public function getSlug()
    {
        return 'gjmaa_settings';
    }
    
    public function getRedirectUri(){
        return admin_url('admin.php?page=gjmaa_settings&action=authorisedCode');
    }
    
    public function getIndexUrl($parent = false)
    {
        return $parent ? parent::getIndexUrl() : admin_url('admin.php?page='.$this->getSlug());
    }
}

?>