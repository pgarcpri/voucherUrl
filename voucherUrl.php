<?php

class voucherUrl extends Module{
    
	public function __construct(){
        $this->name = 'voucherUrl';
        $this->tab = 'advertising_marketing';
        $this->version = 0.1;
        $this->author = 'Pablo Garcia';

        parent::__construct();

        /* Nombre y descripcion que se muestra en la seccion de modulos */

        $this->displayName = $this->l('Vales Descuento en Url');
        $this->description = $this->l('Permite añadir vales descuento por parametro (\'voucher\') en cualquier lugar');
    }
	
    public function install(){
        if (!parent::install() OR
            !$this->registerHook('header') OR
            !$this->registerHook('cart')
        )
            return false;
        return true;
    }

    public function unistall(){
	
        return parent::unistall();
    }

    public function hookHeader(){
        global $cookie;
        if (Tools::getValue('voucher')){
            $cookie->voucherCode = Tools::getValue('voucher');
        }
    }

    public function hookCart($params){

        if (isset($params['cookie']->voucherCode )){

            $vouchersInCart = $params['cart']->getDiscounts();
            $vouchersInCartIds = array();
            if (count($vouchersInCart)>0){
                foreach($vouchersInCart as $voucher){
                    $vouchersInCartIds[] = $voucher['id_discount'];
                }
            }
            $idDiscount = Discount::getIdByName($params['cookie']->voucherCode);

            if (in_array($idDiscount,$vouchersInCartIds)){
                unset($params['cookie']->voucherCode);
            }
            else{
                $discountObj = New Discount($idDiscount);
                $totalType = Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING;
                if ($params['cart']->getOrderTotal(true,$totalType) >= Configuration::get(PS_PURCHASE_MINIMUM) && $params['cart']->getOrderTotal(true,$totalType) >= $discountObj->minimal){
                        $params['cart']->addDiscount($idDiscount);
                        unset($params['cookie']->voucherCode);
                }
            }
        }
    }
}

?>
