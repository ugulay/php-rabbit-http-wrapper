# php-rabbit-http-wrapper

Örnek Kullanımı ( Sample ) : 

        <?php

          $config = [
              'rabbitHost' => '127.0.0.1',
              'rabbitPort' => '15672',
              'rabbitUser' => 'JohnDoe',
              'rabbitPass' => 'foobar'
          ];

          $rabbit = new \Library\RabbitApi($config);

          //Mevcut istatistiklerini çevirir.
          //Return overview/stats
          $data = $rabbit->getInfo()->result();
          $res['message_stats'] = $data['message_stats'];
          $res['queue_totals'] = $data['queue_totals'];
          $res['object_totals'] = $data['object_totals'];
          echo json_encode($res);

          //Sanal Makinadaki Kuyruk Adını çevirir
          //Return VHost name
          $vh = $rabbit->getVirtualHost()->result();
          
          //Kuyruk Listesini getirir
          //Returns Queue List
          $qlist = $rabbit->getQueues->result();
          
        ?>
