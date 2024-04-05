pipeline {
  agent any

  environment {
    DOCKER_COMPOSE_FILE = "docker-compose.yml"
    AWS_INSTANCE_IP = '15.222.242.165'
    GIT_REPO_URL = 'https://github.com/saikiran19999/logistics-supply.git'
  }

  stages {
    stage('Checkout') {
      steps {
        echo "Started Checkout from the github repo...."
        checkout([$class: 'GitSCM',
          branches: [
            [name: "*/master"]
          ],
          doGenerateSubmoduleConfigurations: false,
          extensions: [
            [$class: 'CleanCheckout']
          ],
          submoduleCfg: [],
          userRemoteConfigs: [
            [url: GIT_REPO_URL]
          ]
        ])
        echo "Checkout from the github repo completed...."
      }
    }

    stage('Build and Push Docker Images') {
      steps {
        echo "Login into the Docker using docker hub credentials....."
        script {
          withCredentials([
            [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
          ]) {
            sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"

            echo "Docker Login completed successfully....."

            sh "docker-compose -f ${DOCKER_COMPOSE_FILE} build"

            // Tag and push php-app-web-web image
            echo "Tagging the Front End application image to the repo in the docker hub......"
            sh "docker tag php-app-web-web:latest saykerun1999/logistics-supply-chain:php-app-web-web"
            echo "Pushing the Front End application image to the repo in the docker hub......"
            sh "docker push saykerun1999/logistics-supply-chain:php-app-web-web"

            // Tag and push mysql image
            echo "Tagging the My SQL DB application image to the repo in the docker hub......"
            sh "docker tag mysql:latest saykerun1999/logistics-supply-chain:mysql"
            echo "Pushing the My SQL application image to the repo in the docker hub......"
            sh "docker push saykerun1999/logistics-supply-chain:mysql"

            // Tag and push phpmyadmin image
            echo "Tagging the PHP MY ADMIN application image to the repo in the docker hub......"
            sh "docker tag phpmyadmin/phpmyadmin:latest saykerun1999/logistics-supply-chain:phpmyadmin"
            echo "Pushing the PHP MY ADMIN application image to the repo in the docker hub......"
            sh "docker push saykerun1999/logistics-supply-chain:phpmyadmin"
          }
        }
      }
    }

    stage('Deploy to EC2') {
      steps {
        script {
          echo "Logging into EC2 instance Production....."
          sshagent(['prod_ssh_key_id']) {
            
            echo "Logged into EC2 instance Production....."
            withCredentials([
              [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
            ]) {
              echo "Login into the Docker using docker hub credentials....."
              sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD'"
              def isMySQLRunning = sh(script: "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker inspect -f {{.State.Running}} mysql'", returnStatus: true)
              if (isMySQLRunning == 0) {
                echo "NO NEED TO PULL THE MY SQL AND PHP MY ADMIN CONTAINERS......."
              } else {
                echo "Pulling the BACKEND DB MY SQL IMAGE......"
                sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker pull saykerun1999/logistics-supply-chain:mysql'"
                echo "Pulling the BACKEND PHP MY ADMIN IMAGE......"
                sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker pull saykerun1999/logistics-supply-chain:phpmyadmin'"
                echo "Pulling the FRONT END IMAGE......"
              }
              sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker pull saykerun1999/logistics-supply-chain:php-app-web-web'"
              echo "RUNNING THE MY SQL DB IMAGE USING ON PHP NETWORK THAT IS CREATED ON PRODCTION WITH CREDENTIALS ON PORT 3306......"
              if (isMySQLRunning == 0) {
                echo "NO NEED TO RUN THE MY SQL AND PHP MY ADMIN CONTAINERS......."
              } else {
                sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker run -d --name mysql --network php-network -e MYSQL_ROOT_PASSWORD=sai -e MYSQL_DATABASE=cms_db -e MYSQL_USER=sai -e MYSQL_PASSWORD=sai -p 6033:3306 saykerun1999/logistics-supply-chain:mysql'"
                echo "RUNNING THE MY BACKEND PHP ADMIN IMAGE USING ON PHP NETWORK THAT IS CREATED ON PRODCTION WITH CREDENTIALS ON PORT 82......"
                sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker run -d --name phpmyadmin --network php-network -p 82:80 --link mysql:db -e PMA_HOST=db -e MYSQL_ROOT_PASSWORD=sai -e MYSQL_USER=sai -e MYSQL_PASSWORD=sai saykerun1999/logistics-supply-chain:phpmyadmin'"
              }
              echo "RUNNING THE FRONT END IMAGE USING ON PHP NETWORK THAT IS CREATED ON PRODCTION WITH CREDENTIALS ON PORT 8008......"
              sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker run -d --name web_app --network php-network -p 8008:80 --link mysql:db -e PMA_HOST=db -e MYSQL_ROOT_PASSWORD=sai -e MYSQL_USER=sai -e MYSQL_PASSWORD=sai saykerun1999/logistics-supply-chain:php-app-web-web'"
            }
          }
        }
      }
    }
  }
}
