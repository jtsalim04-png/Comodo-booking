<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?self $Booking = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'Booking')]
    private Collection $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooking(): ?self
    {
        return $this->Booking;
    }

    public function setBooking(?self $Booking): static
    {
        $this->Booking = $Booking;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(self $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setBooking($this);
        }

        return $this;
    }

    public function removeBooking(self $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getBooking() === $this) {
                $booking->setBooking(null);
            }
        }

        return $this;
    }
}
