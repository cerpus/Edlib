{{- define "helpers.initJob" }}
kind: Job
apiVersion: batch/v1
metadata:
  name: {{ .name }}-startup
  annotations:
    "helm.sh/hook": pre-install,pre-upgrade
    "helm.sh/hook-delete-policy": hook-succeeded
spec:
  template:
    spec:
      restartPolicy: Never
      containers:
        - name: startup
          image: {{ .image }}:{{ .imageTag }}
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
