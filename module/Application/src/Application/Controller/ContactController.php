<?php


namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Form\ContactForm;

class ContactController extends AbstractActionController
{
    public function indexAction()
    {
        $form = new ContactForm();
        $form->get('submit')->setValue('Skicka');
        $request = $this->getRequest();

        if($request->isPost()) {
          //Set inputfilter...?

          // if you wanna fill the form in the same way it was filled...
          $form->setData($request->getPost());

          $data = $request->getPost();

          //Do stuff..
          //Send email w/ SES


          try {
            $today = new \DateTime();


            $body  = "Name: \n" . $data['name'] . "\n";
            $body .= "Email: \n" . $data['email'] . "\n";
            //$body .= "Subject: \n" . $data['subject'] . "\n";
            $body .= "Message: \n" . $data['message'] . "\n";

            // Get the client from the builder by namespace
            $aws = $this->getServiceLocator()->get('AWS');
            $client  = $aws->get('ses');

            $result = $client->sendEmail(
                array(
                // Source is required
                'Source' => 'no-reply@fondout.se',
                // Destination is required
                'Destination' => array(
                    // 'ToAddresses' => array('info@fondout.se'),
                     'ToAddresses' => array(\Aws\Ses\Enum\MailboxSimulator::SUCCESS),

                ),
                // Message is required
                'Message' => array(
                    // Subject is required
                    'Subject' => array(
                        // Data is required
                        'Data' => $data['name'] . ', ' . $data['email'] . ', ' . $today->format('Y-m-d H:i:s')
                    ),
                    // Body is required
                    'Body' => array(
                        'Text' => array(
                            // Data is required
                            'Data' => $body
                        )
                    )
                ),
                'ReplyToAddresses' => array($data['email'])
                )
            );

            echo json_encode($result->toArray());

        } catch (Aws\Ses\Exception\MessageRejectedException $e) {
            // Unable to send mail
            $message = $e->getMessage();

            /*$app->log->addError($message);
            $app->response()->status(400);
            $app->response()->header('X-Status-Reason', $message);*/
            echo json_encode(['error' => $message]);
        } catch (Exception $e) {
            /*
            $app->log->addError($e->getMessage());
            $app->response()->status(500);
            */

        }
        }

        return new ViewModel(array('form' => $form));
    }
}
