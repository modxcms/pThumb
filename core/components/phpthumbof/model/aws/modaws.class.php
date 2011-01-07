<?php
/**
 * Handles S3 operations
 */
class modAws {
    public $s3 = false;
    public $bucket = false;
    
    function __construct(modX &$modx,array $config = array()) {
        $this->modx =& $modx;
        $this->config = array_merge(array(
        ),$config);

        if (!defined('AWS_KEY')) {
            define('AWS_KEY',$modx->getOption('phpthumbof.s3_key',$config,''));
            define('AWS_SECRET_KEY',$modx->getOption('phpthumbof.s3_secret_key',$config,''));
            /*
            define('AWS_ACCOUNT_ID',$modx->getOption('aws.account_id',$config,''));
            define('AWS_CANONICAL_ID',$modx->getOption('aws.canonical_id',$config,''));
            define('AWS_CANONICAL_NAME',$modx->getOption('aws.canonical_name',$config,''));
            define('AWS_MFA_SERIAL',$modx->getOption('aws.mfa_serial',$config,''));
            define('AWS_CLOUDFRONT_KEYPAIR_ID',$modx->getOption('aws.cloudfront_keypair_id',$config,''));
            define('AWS_CLOUDFRONT_PRIVATE_KEY_PEM',$modx->getOption('aws.cloudfront_private_key_pem',$config,''));
            define('AWS_ENABLE_EXTENSIONS', 'false');*/
        }
        include dirname(__FILE__).DIRECTORY_SEPARATOR.'sdk.class.php';

        $this->getS3();
        $this->setBucket($modx->getOption('phpthumbof.s3_bucket',$config,''));
    }

    public function getS3() {
        if ($this->s3) return $this->s3;
        
        $this->s3 = new AmazonS3();
        return $this->s3;
    }

    public function setBucket($bucket) {
        $this->bucket = $bucket;
    }
    public function bucketExists() {
        return $this->s3->if_bucket_exists($this->bucket);
    }
    public function createBucket($region = AmazonS3::REGION_US_W1) {
        $response = $this->s3->create_bucket($this->bucket,$region);
	return $response->isOK() ? true : false;
    }

    public function upload($file,$target = '',array $options = array()) {
        $options = array_merge(array(
            'acl' => AmazonS3::ACL_PUBLIC,
        ),$options);

        $individualFiles = array();
        if (is_array($file)) {
            $filename = basename($file);
            $file = $file['tmp_name'];
        } else {
            $filename = basename($file);
        }
        
        $options['fileUpload'] = $file;
        $response = $this->s3->create_object($this->bucket,$target.$filename,$options);
        if ($response->status != 200) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[phpthumbof] Failed uploading '.$file.' to AWS in dir: '.$this->bucket.'/'.$target.' - '.(string)$response->body->Message);
            return false;
        }
        return $this->s3->get_object_url($this->bucket,$target.$filename);
    }

    public function getFileUrl($path,$expires = null) {
        return $this->s3->get_object_url($this->bucket,$path,$expires);
    }

    public function getObjectList($path = '',$opt = array()) {
        if (!empty($path)) {
            $opt['prefix'] = $path;
        }
        $objs = $this->s3->list_objects($this->bucket,$opt);
        $list = array();
        if ($objs && is_object($objs) && $objs->body && $objs->status == 200) {
            foreach ($objs->body->Contents as $obj) {
                $list[] = $obj;
            }
        }
        return $list;
    }

    public function deleteObject($path,$opt = array()) {
        return $this->s3->delete_object($this->bucket,$path,$opt);
    }
}