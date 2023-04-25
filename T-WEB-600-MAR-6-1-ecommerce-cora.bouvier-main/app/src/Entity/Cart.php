<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCarts'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['getCarts'])]
    private ?int $idCart = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getCarts'])]
    private ?string $idProducts_Cart = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Groups(['getCarts'])]
    private ?string $totalPrice = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getCarts'])]
    private ?bool $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getCarts'])]
    private ?string $created_date = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCart(): ?int
    {
        return $this->idCart;
    }

    public function setIdCart(int $idCart): self
    {
        $this->idCart = $idCart;

        return $this;
    }

    public function getIdProductsCart(): ?string
    {
        return $this->idProducts_Cart;
    }

    public function setIdProductsCart(?string $idProducts_Cart): self
    {
        $this->idProducts_Cart = $idProducts_Cart;

        return $this;
    }


    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedDate(): ?string
    {
        return $this->created_date;
    }

    public function setCreatedDate(?string $created_date): self
    {
        $this->created_date = $created_date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


}
