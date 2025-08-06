<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Service for sending emails with attachments
 * Reuses existing Gmail credentials but makes them configurable
 */
class EmailService
{
    private $mailer;
    private $config;
    
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->loadConfig();
        $this->setupMailer();
    }
    
    /**
     * Load email configuration
     * TODO: Make these configurable from frontend settings
     */
    private function loadConfig()
    {
        // TODO: Load from database settings table instead of hardcoded values
        $this->config = [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_username' => 'tianryno01@gmail.com',
            'smtp_password' => 'axms oobi witf ytqa', // Gmail App Password
            'smtp_secure' => PHPMailer::ENCRYPTION_STARTTLS,
            'from_email' => 'tianryno01@gmail.com',
            'from_name' => 'Modular System'
        ];
    }
    
    /**
     * Setup PHPMailer configuration
     */
    private function setupMailer()
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp_username'];
            $this->mailer->Password = $this->config['smtp_password'];
            $this->mailer->SMTPSecure = $this->config['smtp_secure'];
            $this->mailer->Port = $this->config['smtp_port'];
            
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mailer->isHTML(true);
        } catch (Exception $e) {
            error_log('EmailService setup error: ' . $e->getMessage());
            throw new \Exception('Failed to setup email service: ' . $e->getMessage());
        }
    }
    
    /**
     * Send email with optional attachment
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string|null $attachmentPath Path to attachment file
     * @param string|null $attachmentName Custom name for attachment
     * @return array Result with success status and message
     */
    public function sendEmail($to, $subject, $body, $attachmentPath = null, $attachmentName = null)
    {
        try {
            // Reset mailer for new email
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Set recipient
            $this->mailer->addAddress($to);
            
            // Set subject and body
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            
            // Add attachment if provided
            if ($attachmentPath && file_exists($attachmentPath)) {
                $this->mailer->addAttachment($attachmentPath, $attachmentName);
            }
            
            // Send email
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];
            
        } catch (Exception $e) {
            error_log('EmailService send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send OTP email (existing functionality)
     * 
     * @param string $to Recipient email
     * @param string $name Recipient name
     * @param string $otp OTP code
     * @param string $accountNumber Account number
     * @return array Result with success status and message
     */
    public function sendOtpEmail($to, $name, $otp, $accountNumber)
    {
        $subject = 'Password Reset Request';
        $resetLink = "http://yourdomain.com/pages/passreset.html?account_number=" . urlencode($accountNumber);
        
        $body = "Dear $name,<br><br>";
        $body .= "Your account number is: $accountNumber<br><br>";
        $body .= "To reset your password, please click the link below:<br>";
        $body .= "<a href='$resetLink'>$resetLink</a><br><br>";
        $body .= "Best regards,<br>The Modular Team";
        
        return $this->sendEmail($to, $subject, $body);
    }
    
    /**
     * Update email configuration
     * TODO: Implement this to allow frontend configuration
     * 
     * @param array $config New configuration
     * @return bool Success status
     */
    public function updateConfig($config)
    {
        // TODO: Save to database settings table
        // TODO: Validate configuration before saving
        // TODO: Test connection with new settings
        
        return true;
    }
} 