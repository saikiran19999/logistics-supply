pipeline {
    agent any

	environment {
        DOCKER_COMPOSE_FILE = "docker-compose.yml"
        AWS_INSTANCE_IP = '35.183.12.38'
        GIT_BRANCH = 'master' // Change this to your desired branch
    }

    stages {
		stage('Checkout') {
            steps {
                checkout([$class: 'GitSCM',
                          branches: [[name: "*/$GIT_BRANCH"]],
                          doGenerateSubmoduleConfigurations: false,
                          extensions: [[$class: 'CleanCheckout']],
                          submoduleCfg: [],
                          userRemoteConfigs: [[url: 'https://github.com/saikiran19999/logistics-supply.git']]])
            }
        }

        stage('Build Docker Images') {
            steps {
                script {
                    sh "docker-compose -f ${DOCKER_COMPOSE_FILE} build"
                }
            }
        }
		
		stage('Push to Docker Hub') {
                  steps {
                      script {
                          withDockerRegistry(
                              credentialsId: "docker-hub-credentials",
                              url: 'https://index.docker.io/v1/'
                          ) {
                              sh "docker-compose -f ${DOCKER_COMPOSE_FILE} push"
                          }
                      }
                  }
              }

        stage('Deploy to EC2') {
            steps {
                script {
                    sshagent(['prod_ssh_key_id']) {
                        sh "scp -o StrictHostKeyChecking=no ${DOCKER_COMPOSE_FILE} ec2-user@${AWS_INSTANCE_IP}:~/"
                        sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker-compose -f ~/docker-compose.yml pull'"
                        sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker-compose -f ~/docker-compose.yml up -d'"
                    }
                }
            }
        }
    }
}
