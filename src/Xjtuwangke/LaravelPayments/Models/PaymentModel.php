<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14/10/24
 * Time: 21:55
 */

namespace Xjtuwangke\LaravelPayments\Models;


use Illuminate\Database\Schema\Blueprint;
use Omnipay\Omnipay;

class PaymentModel extends \BasicModel{

    protected $table = 'payments';

    public static function _schema( Blueprint $table ){
        $table = parent::_schema( $table );
        $table->string( 'name' );
        $table->string( 'title' );
        $table->text( 'desc' )->nullable();
        $table->text( 'logo' )->nullable();
        $table->text( 'partner_id' )->nullable();
        $table->text( 'partner_key' )->nullable();
        $table->text( 'seller_email' )->nullable();
        $table->text( 'options' )->nullable();
        return $table;
    }

    public function getPartnerIdAttribute( $value ){
        return \Crypt::decrypt( $value );
    }

    public function getPartnerKeyAttribute( $value ){
        return \Crypt::decrypt( $value );
    }

    public function getSellerEmailAttribute( $value ){
        return \Crypt::decrypt( $value );
    }

    public function getOptionsAttribute( $value ){
        if( $value ){
            return json_decode( $value , true );
        }
        else{
            return array();
        }
    }

    public function setPartnerIdAttribute( $value ){
        $this->attributes['partner_id'] = \Crypt::encrypt( $value );
    }

    public function setPartnerKeyAttribute( $value ){
        $this->attributes['partner_key'] = \Crypt::encrypt( $value );
    }

    public function setSellerEmailAttribute( $value ){
        $this->attributes['seller_email'] = \Crypt::encrypt( $value );
    }

    public function setOptionsAttribute( $value ){
        $this->attributes['options'] = json_encode( $value );
    }

    public function getOmniPayGateway(){
        $gateway    = Omnipay::create( $this->name );
        $gateway->setPartner( $this->partner_id );
        $gateway->setKey( $this->partner_key );
        $gateway->setSellerEmail( $this->seller_email );
        return $gateway;
    }
}