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

        stage('Build Docker Image') {
            steps {
                script {
                    // Build Docker image using Dockerfile
                    docker.build("${DOCKER_IMAGE_NAME}:latest", "-f Dockerfile .")
                }
            }
        }

        stage('Update Docker Image in Docker Compose') {
            steps {
                script {
                    // Update Docker image reference in Docker Compose file
                    sh "sed -i 's|image:.*|image: ${DOCKER_IMAGE_NAME}:latest|' ${DOCKER_COMPOSE_FILE}"
                }
            }
        }

        stage('Push to Docker Hub') {
            steps {
                script {
                    // Push Docker image to Docker Hub
                    withDockerRegistry(
                        credentialsId: "docker-hub-credentials",
                        url: 'https://index.docker.io/v1/'
                    ) {
                        sh "docker push ${DOCKER_IMAGE_NAME}:latest"
                    }
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
