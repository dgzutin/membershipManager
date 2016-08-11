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
        
        return $items;

    }
    
    public function getTotalPrice($items)
    {
        $totalPrice = 0;
        foreach ($items as $item){

            $totalPrice = $totalPrice + $item->getTotalPrice();
        }
        return $totalPrice;
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
            ->where('cart.typeAlias = :typeAlias')
            ->andWhere('cart.userId = :userId')
            ->setParameter('typeAlias', $membershipType->getTypeAlias())
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneorNullResult();

        if ($cartItem == NULL){

            $cartItem = new ShoppingCartItem();

            $cartItem->setDescription('');
            $cartItem->setName($membershipType->getTypeName());
            $cartItem->settypeAlias($membershipType->getTypeAlias());
            $cartItem->setQuantity(1);
            $cartItem->setUserID($userId);
            $cartItem->setTotalPrice($membershipType->getFee());
            $cartItem->setUnitPrice($membershipType->getFee());

            $this->em->persist($cartItem);
        }
        else{
            $quantity = $cartItem->getQuantity();
            $cartItem->setQuantity($quantity + 1);
            $cartItem->setTotalPrice($cartItem->getTotalPrice()+ $membershipType->getFee());
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

    
}