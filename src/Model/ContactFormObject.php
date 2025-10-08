<?php
namespace App\Model;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Constraint;

/**
 * Class ContactFormObject
 * @author Boris Bruyère
 *
 */
class ContactFormObject {

    /**
     * @NotBlank(message="form.blank")
     * @Constraint\Length(
     *      max = "100",
     *      maxMessage = "form.max"
     * )
     */
    private $name;

    /**
     * @Constraint\Length(
     *      max = "180",
     *      maxMessage = "contact.form.name.length"
     * )
     */
    private $subject;

    /**
     * @NotBlank(message="form.blank")
     * @Constraint\Email(
     *     message = "form.email"
     * )
     * @Constraint\Length(
     *      max = "255",
     *      maxMessage = "form.max"
     * )
     *
     */
    private $email;

    /**
     * @Constraint\Length(
     *      max = "10000",
     *      maxMessage = "form.max"
     * )
     */
    private $content;


    /**
     */
    private $phone;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
}