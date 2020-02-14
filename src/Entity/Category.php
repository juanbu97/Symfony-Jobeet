<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category{

    //properties

    public function __construct(){
        $this->jobs = new ArrayCollection();
        $this->affiliates = new ArrayCollection();
    }
    /**
     * @var string
     * 
     * @Gedmo\Slug(fields={"name"})
     * 
     * @ORM\Column(type="string", length=128, unique=true)
     */
    private $slug;

    /**
     * @return string/null
     */
    public function getSlug() : ?string{
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void{
        $this->slug = $slug;
    }


    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var Job[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Job", mappedBy="category", cascade={"remove"})
     */
    private $jobs;

    /**
     * @var Affiliate[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Affiliate", mappedBy="categories")
     */
    private $affiliates;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Job[]
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

     /**
     * @return Job[] ArrayCollection
     */
    public function getActiveJobs(){
        return $this->jobs->filter(function(Job $job){
            return $job->getExpiresAt() > new \DateTime() && $job->getActivated();
        });
    }

    public function addJob(Job $job): self
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs[] = $job;
            $job->setCategory($this);
        }

        return $this;
    }

    public function removeJob(Job $job): self
    {
        if ($this->jobs->contains($job)) {
            $this->jobs->removeElement($job);
            // set the owning side to null (unless already changed)
            if ($job->getCategory() === $this) {
                $job->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Affiliate[]
     */
    public function getAffiliates(): Collection
    {
        return $this->affiliates;
    }

    public function addAffiliate(Affiliate $affiliate): self
    {
        if (!$this->affiliates->contains($affiliate)) {
            $this->affiliates[] = $affiliate;
            $affiliate->addCategory($this);
        }

        return $this;
    }

    public function removeAffiliate(Affiliate $affiliate): self
    {
        if ($this->affiliates->contains($affiliate)) {
            $this->affiliates->removeElement($affiliate);
            $affiliate->removeCategory($this);
        }

        return $this;
    }

    
}