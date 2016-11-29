<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 09.08.16
 * Time: 18:33
 */
namespace App\Service;

use App\Entity\MembershipType;
use App\Entity\ShoppingCartItem;

class ShoppingCartServices
{

    public function __construct($container)
    {
        $this->em = $container['em'];
    }

    public function addItem($shoppingCartItem)
    {
        $userId = $_SESSION['user_id'];


    }

    public function getItems()
    {
        $userId = $_SESSION['user_id'];

        $repository =$this->em->getRepository('App\Entity\ShoppingCartItem');
        $items = $repository->createQueryBuilder('cart')
            ->select('cart')
            ->where('cart.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        if (count($items) == 0){
            return array('exception' => true,
                         'message' => "No item was found in the shopping cart");
        }

        return array('exception' => false,
                     'items' => $items,
                     'message' => "Item(s) found");

    }
    
    public function getTotalPrice($items)
    {
        $totalPrice = 0;
        foreach ($items as $item){

            $unitPrice = $item->getUnitPrice();
            $quantity = $item->getQuantity();

           $totalPrice = $totalPrice + ($unitPrice*$quantity);
        }

        return $totalPrice;
    }

    //Convert all amounts in the items to locale settings for the view
    public function convertAmountToLocaleSettings($amount)
    {
        return number_format($amount,2,",",".");
    }

    public function convertAmountsToLocaleSettings($items)
    {
        $i =0;
        foreach ($items as $item) {

            //TODO: get locale settings from config, DB, etc..
            $items[$i]->setUnitPrice(number_format($item->getUnitPrice(),2,",","."));
            $items[$i]->setTotalPrice(number_format($item->getTotalPrice(),2,",","."));
        }
        return $items;
    }

    public function addIMembershipToCart($membershipTypeId)
    {
        $userId = $_SESSION['user_id'];

        $repository =$this->em->getRepository('App\Entity\MembershipType');
        $membershipType = $repository->createQueryBuilder('memberships')
            ->select('memberships')
            ->where('memberships.id = :id')
            ->setParameter('id', $membershipTypeId)
            ->getQuery()
            ->getOneorNullResult();

        $repository =$this->em->getRepository('App\Entity\ShoppingCartItem');
        $cartItem = $repository->createQueryBuilder('cart')
            ->select('cart')
            ->where('cart.typeId = :typeId')
            ->andWhere('cart.userId = :userId')
            ->setParameter('typeId', $membershipTypeId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneorNullResult();

        if ($cartItem == NULL){

            $cartItem = new ShoppingCartItem();

            $cartItem->setDescription('');
            $cartItem->setName($membershipType->getTypeName());
            $cartItem->setTypeId($membershipType->getId());
            $cartItem->setQuantity(1);
            $cartItem->setUserID($userId);
            $cartItem->setTotalPrice($membershipType->getFee());
            $cartItem->setUnitPrice($membershipType->getFee());

            $this->em->persist($cartItem);
        }
        else{

            // Only one membership (quantity = 1) is allowed per user
            //$quantity = $cartItem->getQuantity();
            //$cartItem->setQuantity($quantity + 1);
            //$cartItem->setTotalPrice($cartItem->getTotalPrice()+ $membershipType->getFee());
        }

        try{
            $this->em->flush();
            $result = array('exception' => false,
                'message' => "Item was added to the cart");
        }
        catch (\Exception $e){
            $result = array('exception' => true,
                'message' => $e->getMessage());
        }


        return $result;

    }

    //Remove an item from shopping cart. If $quantity = NULL removes all

    public function removeItemFromCart($userId, $itemId, $quantityToRemove)
    {
        $repository =$this->em->getRepository('App\Entity\ShoppingCartItem');
        $cartItem = $repository->createQueryBuilder('cartItem')
            ->select('cartItem')
            ->where('cartItem.id = :id')
            ->andWhere('cartItem.userId = :userId')
            ->setParameter('userId', $userId)
            ->setParameter('id', $itemId)
            ->getQuery()
            ->getOneorNullResult();

        if ($cartItem != NULL){

            if (($quantityToRemove == NULL) OR ($cartItem->getQuantity() == 1)){
                $this->em->remove($cartItem);
            }
            else{
                $cartItem->setQuantity($cartItem->getQuantity() - $quantityToRemove);
                $cartItem->setTotalPrice($cartItem->getTotalPrice() - ($cartItem->getQuantity() * $cartItem->getUnitPrice()));
                $this->em->persist($cartItem);
            }

            try{
                $this->em->flush();
                $result = array('exception' => false,
                    'message' => "Item(s) was/were removed");
            }
            catch (\Exception $e){
                $result = array('exception' => true,
                    'message' => $e->getMessage());
            }
        }

        return $result;

    }

    public function emptyCart()
    {
        $userId = $_SESSION['user_id'];
        $repository =$this->em->getRepository('App\Entity\ShoppingCartItem');
        $cartItems = $repository->createQueryBuilder('cartItem')
            ->select('cartItem')
            ->andWhere('cartItem.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        if (count($cartItems) != 0){

            $result = array('exception' => false,
                            'message' => "Item(s) was/were removed");
            foreach ($cartItems as $cartItem){

                $this->em->remove($cartItem);
                try{
                    $this->em->flush();
                }
                catch (\Exception $e){
                    $result = array('exception' => true,
                                    'message' => $e->getMessage());
                }
            }
        }


    }

    
}