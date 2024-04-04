pipeline {
  agent any

  environment {
    DOCKER_COMPOSE_FILE = "docker-compose.yml"
    AWS_INSTANCE_IP = '3.96.170.84'
    DOCKER_HUB_USERNAME = 'your_dockerhub_username'
    GIT_REPO_URL = 'https://github.com/saikiran19999/logistics-supply.git'
  }

  stages {
    stage('Checkout') {
      steps {
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
      }
    }

    stage('Build and Push Docker Images') {
      steps {
        script {
          withCredentials([
            [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
          ]) {
            sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"

            sh "docker-compose -f ${DOCKER_COMPOSE_FILE} build"

            // Tag and push php-app-web-web image
            sh "docker tag php-app-web-web:latest saykerun1999/logistics-supply-chain:php-app-web-web"
            sh "docker push saykerun1999/logistics-supply-chain:php-app-web-web"

            // Tag and push mysql image
            sh "docker tag mysql:latest saykerun1999/logistics-supply-chain:mysql"
            sh "docker push saykerun1999/logistics-supply-chain:mysql"

            // Tag and push phpmyadmin image
            sh "docker tag phpmyadmin/phpmyadmin:latest saykerun1999/logistics-supply-chain:phpmyadmin"
            sh "docker push saykerun1999/logistics-supply-chain:phpmyadmin"
          }
        }
      }
    }

    stage('Deploy to EC2') {
      steps {
        script {
          sshagent(['prod_ssh_key_id']) {
            withCredentials([
              [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
            ]) {
              sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD'"
              sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker create network php-network'"
              sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker pull saykerun1999/logistics-supply-chain:mysql'"
              sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker pull saykerun1999/logistics-supply-chain:phpmyadmin'"
              sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker pull saykerun1999/logistics-supply-chain:php-app-web-web'"
            }
          }
        }
      }
    }
  }
}
