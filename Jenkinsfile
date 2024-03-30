pipeline {
  agent any

  environment {
    DOCKER_IMAGE = "php_web_app"
    DOCKER_COMPOSE_FILE = "docker-compose.yml"
    SSH_KEY = credentials('prod_ssh_key_id')
    AWS_INSTANCE_IP = '15.156.93.84'
    GIT_BRANCH = 'master' // Change this to your desired branch
  }

  stages {
    stage('Checkout') {
      steps {
        checkout([$class: 'GitSCM',
          branches: [
            [name: "*/$GIT_BRANCH"]
          ],
          doGenerateSubmoduleConfigurations: false,
          extensions: [
            [$class: 'CleanCheckout']
          ],
          submoduleCfg: [],
          userRemoteConfigs: [
            [url: 'https://github.com/saikiran19999/logistics-supply.git']
          ]
        ])
      }
    }

    stage('Build Docker Images') {
      steps {
        script {
          sh "docker build -t ${DOCKER_IMAGE} ."
          sh "docker-compose -f ${DOCKER_COMPOSE_FILE} build"
        }
      }
    }
    stage('Push Docker Images to Docker Hub') {
      steps {
        script {
          // Tagging and pushing the first image
          sh 'docker tag ${DOCKER_IMAGE} saykerun1999/logistics-supply-chain:newimagev1'

          withCredentials([
            [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
          ]) {
            sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"
          }
          sh 'docker push ${DOCKER_IMAGE} saykerun1999/logistics-supply-chain:newimagev1'

          // Tagging and pushing the second image
          sh 'docker tag mysql:latest saykerun1999/logistics-supply-chain:newimagev2'
          sh 'docker push saykerun1999/logistics-supply-chain:newimagev2'

          //tagging and pushing the 3rd image
          sh 'docker tag phpmyadmin:latest saykerun1999/logistics-supply-chain:newimagev3'
          sh 'docker push saykerun1999/logistics-supply-chain:newimagev2'
        }
      }
    }

    stage('Deploy to EC2') {
      steps {
        script {
          sshagent(['prod_ssh_key_id']) {
            sh "scp -o StrictHostKeyChecking=no ${DOCKER_COMPOSE_FILE} ec2-user@${AWS_INSTANCE_IP}:~/"
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker-compose -f ${DOCKER_COMPOSE_FILE} pull'"
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker-compose -f ${DOCKER_COMPOSE_FILE} up -d'"
          }
        }
      }
    }
  }
}
