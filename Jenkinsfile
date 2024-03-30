pipeline {
  agent any

  environment {
    DOCKER_COMPOSE_FILE = "docker-compose.yml"
    SSH_KEY = credentials('prod_ssh_key_id')
    AWS_INSTANCE_IP = '15.156.93.84'
    GIT_BRANCH = 'master' // Change this to your desired branch
    MYSQL_ROOT_PASSWORD = 'sai'
    MYSQL_DATABASE = 'cms_db'
    MYSQL_USER = 'sai'
    MYSQL_PASSWORD = 'sai'
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
          sh "docker-compose -f ${DOCKER_COMPOSE_FILE} build"
        }
      }
    }

    stage('Push Docker Images to Docker Hub') {
      steps {
        script {
          // Tagging and pushing the images
          // Tagging and pushing the first image
          sh "docker tag php-web-app:latest saykerun1999/logistics-supply-chain:newimagev1"
          sh "docker tag mysql:latest saykerun1999/logistics-supply-chain:newimagev2"
          sh "docker tag phpmyadmin:latest saykerun1999/logistics-supply-chain:newimagev3"

          withCredentials([
            [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
          ]) {
            sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"
          }

          sh "docker push saykerun1999/logistics-supply-chain:newimagev1"
          sh "docker push saykerun1999/logistics-supply-chain:newimagev2"
          sh "docker push saykerun1999/logistics-supply-chain:newimagev3"
        }
      }
    }

    stage('Deploy to EC2') {
      steps {
        script {
          withCredentials([
            [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
          ]) {
            sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"
          }
          sshagent(['prod_ssh_key_id']) {
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker pull saykerun1999/logistics-supply-chain:newimagev1'"
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker pull saykerun1999/logistics-supply-chain:newimagev2'"
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker pull saykerun1999/logistics-supply-chain:newimagev3'"
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker run -d -p 6033:3306 --name database -e MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD} -e MYSQL_DATABASE=${MYSQL_DATABASE} -e MYSQL_USER=${MYSQL_USER} -e MYSQL_PASSWORD=${MYSQL_PASSWORD} saykerun1999/logistics-supply-chain:newimagev2'"
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker run -d -p 82:80 --name backend saykerun1999/logistics-supply-chain:newimagev3'"
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker run -d -p 8008:80 --name frontend -e BACKEND_URL=http://${AWS_INSTANCE_IP}:82 saykerun1999/logistics-supply-chain:newimagev1'"
          }
        }
      }
    }
  }
}
