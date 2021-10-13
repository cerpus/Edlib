{{- define "helpers.phpDeployment" }}
apiVersion: apps/v1
kind: Deployment
metadata:
  name: {{ .name }}
  labels:
    app: {{ .name }}
spec:
  replicas: {{ .replicaCount }}
  strategy:
    type: RollingUpdate
    rollingUpdate:
      maxSurge: 2
      maxUnavailable: 0
  selector:
    matchLabels:
      app: {{ .name }}
  template:
    metadata:
      labels:
        app: {{ .name }}
    spec:
      affinity:
        podAntiAffinity:
          preferredDuringSchedulingIgnoredDuringExecution:
            - weight: 1
              podAffinityTerm:
                labelSelector:
                  matchExpressions:
                    - key: app
                      operator: In
                      values:
                        - {{ .name }}
                topologyKey: 'kubernetes.io/hostname'
      containers:
        - name: proxy
          image: "{{ .proxyImage }}:{{ .imageTag }}"
          ports:
            - name: http
              containerPort: {{ .port | default "80" }}
              protocol: TCP
          livenessProbe:
            httpGet:
              path: {{ .healthUrl }}
              port: {{ .port | default "80" }}
            timeoutSeconds: 5
            periodSeconds: 10
            successThreshold: 1
            failureThreshold: 10
          readinessProbe:
            httpGet:
              path: {{ .healthUrl }}
              port: {{ .port | default "80" }}
            timeoutSeconds: 5
            periodSeconds: 10
            successThreshold: 1
            failureThreshold: 2
          startupProbe:
            httpGet:
              path: {{ .healthUrl }}
              port: {{ .port | default "80" }}
            initialDelaySeconds: 10
            timeoutSeconds: 5
            periodSeconds: 10
            failureThreshold: 100
          envFrom:
            - configMapRef:
                name: common-config
                optional: true
            - secretRef:
                name: common-secret
                optional: true
        - name: fpm
          image: "{{ .fpmImage }}:{{ .imageTag }}"
          resources:
            requests:
              memory: '256Mi'
              cpu: '20m'
            limits:
              memory: '1024Mi'
              cpu: '1000m'
          envFrom:
            - configMapRef:
                name: common-config
                optional: true
            - secretRef:
                name: common-secret
                optional: true
      imagePullSecrets:
        - name: dockerconfigjson-github-com
{{- end }}
