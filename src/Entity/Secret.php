<?php

namespace App\Entity;

use App\Repository\SecretRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecretRepository::class)]
class Secret
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1000)]
    private ?string $secret = null;

    #[ORM\Column]
    private ?int $expireAfterViews = null;

    #[ORM\Column]
    private ?int $expireAfter = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hash = null;

    public function __construct()
    {
        date_default_timezone_set("Europe/Budapest");
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function getExpireAfterViews(): ?int
    {
        return $this->expireAfterViews;
    }

    public function setExpireAfterViews(int $expireAfterViews): self
    {
        $this->expireAfterViews = $expireAfterViews;

        return $this;
    }

    public function getExpireAfter(): ?int
    {
        return $this->expireAfter;
    }

    public function setExpireAfter(int $expireAfter): self
    {
        $this->expireAfter = $expireAfter;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getExpiresAt()
    {
        $expiresat = $this->getCreatedAt();
        if($this->getExpireAfter() == 0){
            $expiresat = 'never';
        } else {
            $expiresat->modify('+'.$this->getExpireAfter().' minutes'); 
        }

        return $expiresat;
    }

    public function isExpired(): ?bool
    {
        date_default_timezone_set("Europe/Budapest");
        $now = new \DateTime();

        if($this->getExpireAfter() == 0){
            return false;
        }
        if($now > $this->getExpiresAt()){
            return true;
        }
        return false;
    }

    public function createResponseFromSecret()
    {
        $response = [
            'hash' => $this->getHash(),
            'secretText' => $this->getSecret(),
            'createdAt' => $this->getCreatedAt()->modify('- 1 hour'),
            'expiresAt' => $this->getExpiresAt(),
            'remainingViews' => $this->getExpireAfterViews(),
        ];

        return $response;
    }

    public function createSecretXMLResponse()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Secret>';
        $xml .= '<hash>'.$this->getHash().'</hash>';
        $xml .= '<secretText>'.$this->getSecret().'</secretText>';
        $xml .= '<createdAt>'.$this->getCreatedAt()->format('Y-m-d H:i:s').'</createdAt>';
        $xml .= '<expiresAt>'.$this->getExpiresAt()->format('Y-m-d H:i:s').'</expiresAt>';
        $xml .= '<remainingViews>'.$this->getExpireAfterViews().'</remainingViews>';
        $xml .= '</Secret>';    
        
        return $xml;
    }
}
