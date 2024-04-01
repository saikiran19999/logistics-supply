pipeline {
    agent any

    environment {
        DOCKER_COMPOSE_FILE = "docker-compose.yml"
        SSH_KEY = credentials('prod_ssh_key_id')
        AWS_INSTANCE_IP = '3.99.241.216'
        GIT_BRANCH = 'master' // Change this to your desired branch
        MYSQL_ROOT_PASSWORD = 'sai'
        MYSQL_DATABASE = 'cms_db'
        MYSQL_USER = 'sai'
        MYSQL_PASSWORD = 'sai'
        DOCKER_NETWORK = 'php-network'
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
                    withCredentials([
                        [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
                    ]) {
                        sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"
                    }

                    // Tagging and pushing the images
                    sh "docker-compose -f ${DOCKER_COMPOSE_FILE} push"
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
                        sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} sudo chmod -R 755 /var/www/html/"
                        sh "scp -o StrictHostKeyChecking=no docker-compose.yml ec2-user@${AWS_INSTANCE_IP}:~/"
                        sh "scp -o StrictHostKeyChecking=no -r * ec2-user@${AWS_INSTANCE_IP}:/tmp/"
                        sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'sudo mv /tmp/* /var/www/html/'"
                        //sh "scp -o StrictHostKeyChecking=no -r * ec2-user@${AWS_INSTANCE_IP}:/var/www/html/"
                        sh "scp -o StrictHostKeyChecking=no Dockerfile ec2-user@${AWS_INSTANCE_IP}:~/"
                        sh "scp -o StrictHostKeyChecking=no ./database/cms_db.sql ec2-user@${AWS_INSTANCE_IP}:~/"
                        sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker-compose pull'"
                        sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker-compose up -d'"
                        sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker exec mysqli mysql -uroot -psai cms_db < cms_db.sql' > import_log.txt"
                    }
                }
            }
        }
    }
}
