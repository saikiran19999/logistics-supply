pipeline {
    agent any

    environment {
        DOCKER_COMPOSE_FILE = "docker-compose.yml"
        DOCKER_IMAGE_NAME = "php-web-app"
        AWS_INSTANCE_IP = '15.156.93.84'
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

        stage('Push Docker Images to Docker Hub') {
            steps {
                script {
                    // Log in to Docker Hub
                    withCredentials([
                        [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
                    ]) {
                        sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"
                    }
                    
                    // Tag and push all images defined in the Docker Compose file
                    sh "docker-compose -f ${DOCKER_COMPOSE_FILE} push"
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
