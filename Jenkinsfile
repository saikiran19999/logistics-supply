pipeline {
  agent any

  environment {
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
          sh "docker-compose -f ${DOCKER_COMPOSE_FILE} build"
        }
      }
    }

    stage('Push Docker Images to Docker Hub') {
      steps {
        script {
          // Tagging and pushing the first image
          sh "docker tag php-web-app:latest saykerun1999/logistics-supply-chain:newimagev1"
          sh "docker pull mysql:latest"
          sh "docker pull phpmyadmin:latest"

          withCredentials([
            [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
          ]) {
            sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"
          }
          sh "docker push saykerun1999/logistics-supply-chain:newimagev1"

          // Tagging and pushing the second image
          sh "docker tag mysql:latest saykerun1999/logistics-supply-chain:newimagev2"
          sh "docker push saykerun1999/logistics-supply-chain:newimagev2"

          // Tagging and pushing the third image
          sh "docker tag phpmyadmin:latest saykerun1999/logistics-supply-chain:newimagev3"
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
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker run -d -p 6033:3306 --name database saykerun1999/logistics-supply-chain:newimagev2'"
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker run -d -p 82:80 --name backend saykerun1999/logistics-supply-chain:newimagev3'"
            sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker run -d -p 8008:80 --name frontend --link backend:backend saykerun1999/logistics-supply-chain:newimagev1'"
          }
        }
      }
    }
  }
}
